<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use App\Models\ShiftNote;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    /**
     * Display the manager dashboard page view.
     */
    public function index()
    {
        return view('manager');
    }

    /**
     * Get statistics for the dashboard in JSON format.
     */
    public function getStats()
    {
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();
        $yesterdayStart = Carbon::yesterday();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();

        // 1. TODAY'S SALES
        $todaySales = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        $yesterdaySales = Order::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        // Trend calculation for Sales
        $salesTrendPercent = 0;
        $salesTrendDirection = 'up';
        if ($yesterdaySales > 0) {
            $salesTrendPercent = round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100);
            $salesTrendDirection = $salesTrendPercent >= 0 ? 'up' : 'down';
            $salesTrendPercent = abs($salesTrendPercent);
        } else {
            // If yesterday had 0 sales and today has sales, trend is +100%
            $salesTrendPercent = $todaySales > 0 ? 100 : 0;
            $salesTrendDirection = 'up';
        }

        // 2. ORDERS TODAY
        $todayOrders = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $yesterdayOrders = Order::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $ordersDiff = $todayOrders - $yesterdayOrders;
        $ordersTrendDirection = $ordersDiff >= 0 ? 'up' : 'down';
        $ordersDiffText = abs($ordersDiff) . ' ' . ($ordersDiff >= 0 ? 'more' : 'fewer') . ' than yesterday';

        // 3. AVERAGE ORDER VALUE (AOV)
        $todayAOV = $todayOrders > 0 ? ($todaySales / $todayOrders) : 0;
        $yesterdayAOV = $yesterdayOrders > 0 ? ($yesterdaySales / $yesterdayOrders) : 0;

        $aovDiff = round($todayAOV - $yesterdayAOV, 2);
        $aovTrendDirection = $aovDiff >= 0 ? 'up' : 'down';
        $aovDiffText = '₱' . abs($aovDiff) . ' vs yesterday';

        // 4. CHART DATA: LAST 7 DAYS SALES
        $startDate = Carbon::today()->subDays(6)->startOfDay();
        $dailyTotals = Order::select(DB::raw('DATE(created_at) as date_val'), DB::raw('SUM(total) as daily_total'))
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['pending', 'completed'])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('daily_total', 'date_val')
            ->toArray();

        $chartLabels = [];
        $chartValues = []; // in thousands, e.g. 7.4
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $salesForDay = $dailyTotals[$dateKey] ?? 0;

            // Format label as Day name abbreviation (e.g. Mon, Tue, etc.)
            $chartLabels[] = $date->format('D');
            
            // Round to 1 decimal place in thousands, e.g. 7.2k
            $chartValues[] = round($salesForDay / 1000, 1);
        }

        // 5. TOP SELLING ITEMS TODAY (fallback to all-time if today is empty)
        $topItemsToday = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($todayStart, $todayEnd) {
                $query->whereBetween('created_at', [$todayStart, $todayEnd])
                      ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        $isFallback = false;
        if ($topItemsToday->isEmpty()) {
            $isFallback = true;
            // Fallback to all-time top selling
            $topItemsToday = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
                ->whereHas('order', function ($query) {
                    $query->whereIn('status', ['pending', 'completed']);
                })
                ->groupBy('product_name')
                ->orderBy('qty_sold', 'desc')
                ->limit(5)
                ->get();
        }

        // Get total revenue for share calculation
        $totalRevenueForShare = $topItemsToday->sum('revenue');

        $topItemsFormatted = [];
        $categoriesMap = [
            'Americano' => 'Coffee',
            'Cafe Latte' => 'Coffee',
            'Cafe Mocha' => 'Coffee',
            'Matcha Drink' => 'Non-Coffee',
            'Sweetened' => 'Lemonade',
            'Strawberry Drink' => 'Lemonade',
            'Strawberry' => 'Lemonade',
            'Chicken Ala King' => 'Rice Bowls',
            'Sweet Garlic Longganisa' => 'Rice Bowls',
            'Chicken Fried Rice' => 'Rice Bowls',
            'Cheezy Bacon' => 'Rice Bowls',
            'Beef Tapa' => 'Rice Bowls',
        ];

        foreach ($topItemsToday as $item) {
            $sharePercent = $totalRevenueForShare > 0 ? round(($item->revenue / $totalRevenueForShare) * 100) : 0;
            $topItemsFormatted[] = [
                'product_name' => $item->product_name,
                'category' => $categoriesMap[$item->product_name] ?? 'Beverage',
                'qty_sold' => (int) $item->qty_sold,
                'revenue' => (float) $item->revenue,
                'share_percent' => $sharePercent
            ];
        }

        // 6. INVENTORY ALERTS (from database)
        $outOfStockItems = Inventory::where('quantity', 0)->get(['item_name'])->pluck('item_name')->toArray();
        $lowStockItems = Inventory::where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'min_threshold')
            ->get(['item_name', 'quantity'])
            ->toArray();

        // 7. UNRESOLVED SHIFT NOTES
        $unresolvedShiftNotesCount = ShiftNote::where('is_done', false)->count();

        // 8. SALES REPORT ANALYTICS QUICK VIEW
        $todayAnalytics = $this->computePeriodAnalytics($todayStart, $todayEnd, $categoriesMap, $chartLabels, $chartValues, 'daily');

        return response()->json([
            'success' => true,
            'today_sales' => (float) $todaySales,
            'today_sales_trend' => [
                'percent' => $salesTrendPercent,
                'direction' => $salesTrendDirection,
            ],
            'today_orders' => $todayOrders,
            'today_orders_trend' => [
                'text' => $ordersDiffText,
                'direction' => $ordersTrendDirection,
            ],
            'avg_order_value' => round($todayAOV, 2),
            'avg_order_value_trend' => [
                'text' => $aovDiffText,
                'direction' => $aovTrendDirection,
            ],
            'chart_data' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
            ],
            'top_items' => $topItemsFormatted,
            'is_top_items_fallback' => $isFallback,
            'inventory_alerts' => [
                'out_of_stock' => $outOfStockItems,
                'low_stock' => $lowStockItems,
            ],
            'unresolved_shift_notes_count' => $unresolvedShiftNotesCount,
            'sales_analytics' => $todayAnalytics
        ]);
    }

    /**
     * Display the manager-specific shift notes page view.
     */
    public function shiftNotes()
    {
        $notes = ShiftNote::orderBy('created_at', 'desc')->get();
        return view('manager-shift-notes', compact('notes'));
    }

    /**
     * Display the manager-specific sales report page view.
     */
    public function salesReport()
    {
        return view('manager-sales-report');
    }

    /**
     * Get sales report datasets for Daily, Weekly, and Monthly reports.
     */
    public function getSalesData()
    {
        $categoriesMap = [
            'Americano' => 'Coffee',
            'Cafe Latte' => 'Coffee',
            'Cafe Mocha' => 'Coffee',
            'Matcha Drink' => 'Non-Coffee',
            'Sweetened' => 'Lemonade',
            'Strawberry Drink' => 'Lemonade',
            'Strawberry' => 'Lemonade',
            'Chicken Ala King' => 'Rice Bowls',
            'Sweet Garlic Longganisa' => 'Rice Bowls',
            'Chicken Fried Rice' => 'Rice Bowls',
            'Cheezy Bacon' => 'Rice Bowls',
        ];

        // Dynamic products categories map
        $dbCategories = Product::pluck('category', 'name')->toArray();
        $categoriesMap = array_merge($categoriesMap, $dbCategories);

        // --- 1. DAILY STATS ---
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();

        $dailySales = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        $dailyOrders = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $dailyAOV = $dailyOrders > 0 ? round($dailySales / $dailyOrders, 2) : 0;

        // Daily Chart (Hourly buckets from 8 AM to 10 PM, in 2-hour increments)
        $dailyChartLabels = ['08 AM', '10 AM', '12 PM', '02 PM', '04 PM', '06 PM', '08 PM', '10 PM'];
        $dailyChartValues = [];
        foreach ($dailyChartLabels as $hourStr) {
            $hour = intval(substr($hourStr, 0, 2));
            if (strpos($hourStr, 'PM') !== false && $hour != 12) {
                $hour += 12;
            }
            if (strpos($hourStr, 'AM') !== false && $hour == 12) {
                $hour = 0;
            }

            $bucketStart = Carbon::today()->setHour($hour)->startOfHour();
            $bucketEnd = $bucketStart->copy()->addHours(2)->subSecond();

            $sum = Order::whereBetween('created_at', [$bucketStart, $bucketEnd])
                ->whereIn('status', ['pending', 'completed'])
                ->sum('total');
            $dailyChartValues[] = round($sum, 2);
        }

        // Daily Top Selling Items
        $dailyTopItems = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($todayStart, $todayEnd) {
                $query->whereBetween('created_at', [$todayStart, $todayEnd])
                      ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        $dailyMaxQty = $dailyTopItems->first() ? $dailyTopItems->first()->qty_sold : 1;
        $dailyItemsFormatted = [];
        foreach ($dailyTopItems as $item) {
            $share = $dailyMaxQty > 0 ? round(($item->qty_sold / $dailyMaxQty) * 100) : 0;
            $dailyItemsFormatted[] = [
                'product_name' => $item->product_name,
                'category' => $categoriesMap[$item->product_name] ?? 'Beverage',
                'qty_sold' => (int) $item->qty_sold,
                'revenue' => (float) $item->revenue,
                'share_percent' => $share
            ];
        }

        $dailyAnalytics = $this->computePeriodAnalytics($todayStart, $todayEnd, $categoriesMap, $dailyChartLabels, $dailyChartValues, 'daily');

        // --- 2. WEEKLY STATS ---
        $weeklyStart = Carbon::today()->subDays(6)->startOfDay();
        $weeklyEnd = Carbon::today()->endOfDay();

        $weeklySales = Order::whereBetween('created_at', [$weeklyStart, $weeklyEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        $weeklyOrders = Order::whereBetween('created_at', [$weeklyStart, $weeklyEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $weeklyAOV = $weeklyOrders > 0 ? round($weeklySales / $weeklyOrders, 2) : 0;

        // Weekly Chart (Daily totals for last 7 days)
        $weeklyDailyTotals = Order::select(DB::raw('DATE(created_at) as date_val'), DB::raw('SUM(total) as daily_total'))
            ->where('created_at', '>=', $weeklyStart)
            ->whereIn('status', ['pending', 'completed'])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('daily_total', 'date_val')
            ->toArray();

        $weeklyChartLabels = [];
        $weeklyChartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $weeklyChartLabels[] = $day->format('D');
            $dateKey = $day->format('Y-m-d');
            $sum = $weeklyDailyTotals[$dateKey] ?? 0;
            $weeklyChartValues[] = round($sum, 2);
        }

        // Weekly Top Selling Items
        $weeklyTopItems = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($weeklyStart, $weeklyEnd) {
                $query->whereBetween('created_at', [$weeklyStart, $weeklyEnd])
                      ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        $weeklyMaxQty = $weeklyTopItems->first() ? $weeklyTopItems->first()->qty_sold : 1;
        $weeklyItemsFormatted = [];
        foreach ($weeklyTopItems as $item) {
            $share = $weeklyMaxQty > 0 ? round(($item->qty_sold / $weeklyMaxQty) * 100) : 0;
            $weeklyItemsFormatted[] = [
                'product_name' => $item->product_name,
                'category' => $categoriesMap[$item->product_name] ?? 'Beverage',
                'qty_sold' => (int) $item->qty_sold,
                'revenue' => (float) $item->revenue,
                'share_percent' => $share
            ];
        }

        $weeklyAnalytics = $this->computePeriodAnalytics($weeklyStart, $weeklyEnd, $categoriesMap, $weeklyChartLabels, $weeklyChartValues, 'weekly');

        // --- 3. MONTHLY STATS ---
        $monthlyStart = Carbon::today()->subDays(29)->startOfDay();
        $monthlyEnd = Carbon::today()->endOfDay();

        $monthlySales = Order::whereBetween('created_at', [$monthlyStart, $monthlyEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        $monthlyOrders = Order::whereBetween('created_at', [$monthlyStart, $monthlyEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $monthlyAOV = $monthlyOrders > 0 ? round($monthlySales / $monthlyOrders, 2) : 0;

        // Monthly Chart (Weekly buckets for last 4 weeks)
        $monthlyChartLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $monthlyChartValues = [];
        for ($w = 3; $w >= 0; $w--) {
            $bucketStart = Carbon::today()->subWeeks($w)->startOfWeek();
            $bucketEnd = $bucketStart->copy()->endOfWeek();

            $sum = Order::whereBetween('created_at', [$bucketStart, $bucketEnd])
                ->whereIn('status', ['pending', 'completed'])
                ->sum('total');
            $monthlyChartValues[] = round($sum, 2);
        }

        // Monthly Top Selling Items
        $monthlyTopItems = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($monthlyStart, $monthlyEnd) {
                $query->whereBetween('created_at', [$monthlyStart, $monthlyEnd])
                      ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        $monthlyMaxQty = $monthlyTopItems->first() ? $monthlyTopItems->first()->qty_sold : 1;
        $monthlyItemsFormatted = [];
        foreach ($monthlyTopItems as $item) {
            $share = $monthlyMaxQty > 0 ? round(($item->qty_sold / $monthlyMaxQty) * 100) : 0;
            $monthlyItemsFormatted[] = [
                'product_name' => $item->product_name,
                'category' => $categoriesMap[$item->product_name] ?? 'Beverage',
                'qty_sold' => (int) $item->qty_sold,
                'revenue' => (float) $item->revenue,
                'share_percent' => $share
            ];
        }

        $monthlyAnalytics = $this->computePeriodAnalytics($monthlyStart, $monthlyEnd, $categoriesMap, $monthlyChartLabels, $monthlyChartValues, 'monthly');

        return response()->json([
            'success' => true,
            'daily' => [
                'revenue' => (float) $dailySales,
                'orders_count' => $dailyOrders,
                'aov' => $dailyAOV,
                'chart' => [
                    'labels' => $dailyChartLabels,
                    'values' => $dailyChartValues
                ],
                'top_items' => $dailyItemsFormatted,
                'analytics' => $dailyAnalytics
            ],
            'weekly' => [
                'revenue' => (float) $weeklySales,
                'orders_count' => $weeklyOrders,
                'aov' => $weeklyAOV,
                'chart' => [
                    'labels' => $weeklyChartLabels,
                    'values' => $weeklyChartValues
                ],
                'top_items' => $weeklyItemsFormatted,
                'analytics' => $weeklyAnalytics
            ],
            'monthly' => [
                'revenue' => (float) $monthlySales,
                'orders_count' => $monthlyOrders,
                'aov' => $monthlyAOV,
                'chart' => [
                    'labels' => $monthlyChartLabels,
                    'values' => $monthlyChartValues
                ],
                'top_items' => $monthlyItemsFormatted,
                'analytics' => $monthlyAnalytics
            ]
        ]);
    }

    /**
     * Compute rich analytics for sales reporting periods
     */
    private function computePeriodAnalytics($startDate, $endDate, $categoriesMap, $chartLabels, $chartValues, $rangeType = 'daily')
    {
        // 1. Orders and Payment Methods breakdown
        $cashOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'completed'])
            ->where('payment_method', 'CASH');
        $cashRevenue = (float) $cashOrders->sum('total');
        $cashCount = (int) $cashOrders->count();

        $gcashOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'completed'])
            ->where('payment_method', 'GCASH');
        $gcashRevenue = (float) $gcashOrders->sum('total');
        $gcashCount = (int) $gcashOrders->count();

        $totalRevenue = $cashRevenue + $gcashRevenue;
        $cashPercent = $totalRevenue > 0 ? round(($cashRevenue / $totalRevenue) * 100) : 0;
        $gcashPercent = $totalRevenue > 0 ? round(($gcashRevenue / $totalRevenue) * 100) : 0;

        // Financial summary: Gross sales & discounts
        $allOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'completed']);
        $totalOrdersCount = (int) $allOrders->count();
        $discountsTotal = (float) $allOrders->sum('discount_amount');
        $grossSubtotal = (float) $allOrders->sum('subtotal');
        $grossSales = $grossSubtotal > 0 ? $grossSubtotal : ($totalRevenue + $discountsTotal);
        $discountRate = $grossSales > 0 ? round(($discountsTotal / $grossSales) * 100, 1) : 0;

        // 2. Category breakdown
        $items = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                      ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->get();

        $categoryAgg = [];
        $totalItemsSold = 0;
        foreach ($items as $it) {
            $cat = $categoriesMap[$it->product_name] ?? 'Beverage';
            if (!isset($categoryAgg[$cat])) {
                $categoryAgg[$cat] = ['category' => $cat, 'qty_sold' => 0, 'revenue' => 0.0];
            }
            $categoryAgg[$cat]['qty_sold'] += (int) $it->qty_sold;
            $categoryAgg[$cat]['revenue'] += (float) $it->revenue;
            $totalItemsSold += (int) $it->qty_sold;
        }

        // Sort categories by revenue desc
        usort($categoryAgg, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        $categoriesList = [];
        $topCategoryName = 'None';
        $topCategoryShare = 0;
        foreach ($categoryAgg as $c) {
            $share = $totalRevenue > 0 ? round(($c['revenue'] / $totalRevenue) * 100) : 0;
            $categoriesList[] = [
                'category' => $c['category'],
                'qty_sold' => $c['qty_sold'],
                'revenue' => round($c['revenue'], 2),
                'share_percent' => $share
            ];
        }
        if (!empty($categoriesList)) {
            $topCategoryName = $categoriesList[0]['category'];
            $topCategoryShare = $categoriesList[0]['share_percent'];
        }

        // 3. Basket Depth & Velocity
        $avgBasketSize = $totalOrdersCount > 0 ? round($totalItemsSold / $totalOrdersCount, 1) : 0;
        $avgTicket = $totalOrdersCount > 0 ? round($totalRevenue / $totalOrdersCount, 2) : 0;

        // 4. Peak activity window
        $peakLabel = '—';
        $peakRevenue = 0;
        if (!empty($chartValues)) {
            $maxVal = max($chartValues);
            if ($maxVal > 0) {
                $maxIndex = array_search($maxVal, $chartValues);
                $peakLabel = $chartLabels[$maxIndex] ?? '—';
                $peakRevenue = $maxVal;
            }
        }

        // 5. Day-Part / Rush Time Distribution
        $ordersList = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'completed'])
            ->get(['total', 'created_at']);

        $dayParts = [
            'morning'   => ['label' => 'Morning Rush (8am-12pm)', 'hours' => '08:00 - 11:59', 'count' => 0, 'revenue' => 0.0, 'percent' => 0, 'icon' => 'fa-mug-saucer'],
            'lunch'     => ['label' => 'Lunch Peak (12pm-3pm)', 'hours' => '12:00 - 14:59', 'count' => 0, 'revenue' => 0.0, 'percent' => 0, 'icon' => 'fa-utensils'],
            'afternoon' => ['label' => 'Afternoon Chill (3pm-6pm)', 'hours' => '15:00 - 17:59', 'count' => 0, 'revenue' => 0.0, 'percent' => 0, 'icon' => 'fa-cloud-sun'],
            'evening'   => ['label' => 'Evening Surge (6pm-10pm)', 'hours' => '18:00 - 22:00', 'count' => 0, 'revenue' => 0.0, 'percent' => 0, 'icon' => 'fa-moon'],
        ];

        foreach ($ordersList as $ord) {
            $h = Carbon::parse($ord->created_at)->hour;
            $amt = (float) $ord->total;
            if ($h >= 8 && $h < 12) {
                $dayParts['morning']['count']++;
                $dayParts['morning']['revenue'] += $amt;
            } elseif ($h >= 12 && $h < 15) {
                $dayParts['lunch']['count']++;
                $dayParts['lunch']['revenue'] += $amt;
            } elseif ($h >= 15 && $h < 18) {
                $dayParts['afternoon']['count']++;
                $dayParts['afternoon']['revenue'] += $amt;
            } else {
                $dayParts['evening']['count']++;
                $dayParts['evening']['revenue'] += $amt;
            }
        }

        $topDayPartKey = 'morning';
        $topDayPartRev = -1;
        foreach ($dayParts as $k => &$dp) {
            $dp['revenue'] = round($dp['revenue'], 2);
            $dp['percent'] = $totalRevenue > 0 ? round(($dp['revenue'] / $totalRevenue) * 100) : 0;
            if ($dp['revenue'] > $topDayPartRev) {
                $topDayPartRev = $dp['revenue'];
                $topDayPartKey = $k;
            }
        }
        unset($dp);

        // 6. Target Milestone Progress
        $targetGoals = [
            'daily'   => 15000,
            'weekly'  => 85000,
            'monthly' => 320000,
        ];
        $goal = $targetGoals[$rangeType] ?? 15000;
        $achievedPercent = $goal > 0 ? round(($totalRevenue / $goal) * 100, 1) : 0;
        $remaining = max(0, $goal - $totalRevenue);

        return [
            'financial_summary' => [
                'gross_sales' => round($grossSales, 2),
                'discounts_total' => round($discountsTotal, 2),
                'discount_rate' => $discountRate,
                'net_sales' => round($totalRevenue, 2),
                'avg_basket_items' => $avgBasketSize,
                'avg_order_value' => $avgTicket,
                'total_orders' => $totalOrdersCount,
                'total_items_sold' => $totalItemsSold
            ],
            'target_progress' => [
                'goal' => $goal,
                'current' => round($totalRevenue, 2),
                'percent' => $achievedPercent,
                'remaining' => round($remaining, 2),
                'status' => $achievedPercent >= 100 ? 'Achieved' : 'On Track'
            ],
            'day_parts' => $dayParts,
            'top_day_part' => $dayParts[$topDayPartKey]['label'],
            'payment_methods' => [
                'cash_revenue' => $cashRevenue,
                'cash_count' => $cashCount,
                'cash_percent' => $cashPercent,
                'cash_avg' => $cashCount > 0 ? round($cashRevenue / $cashCount, 2) : 0,
                'gcash_revenue' => $gcashRevenue,
                'gcash_count' => $gcashCount,
                'gcash_percent' => $gcashPercent,
                'gcash_avg' => $gcashCount > 0 ? round($gcashRevenue / $gcashCount, 2) : 0,
            ],
            'categories' => $categoriesList,
            'insights' => [
                'peak_label' => $peakLabel,
                'peak_revenue' => $peakRevenue,
                'total_items_sold' => $totalItemsSold,
                'avg_basket_items' => $avgBasketSize,
                'top_category' => $topCategoryName,
                'top_category_share' => $topCategoryShare,
                'top_payment_method' => $cashRevenue >= $gcashRevenue ? 'Cash' : 'GCash',
                'top_payment_percent' => $cashRevenue >= $gcashRevenue ? $cashPercent : $gcashPercent,
                'top_rush_period' => $dayParts[$topDayPartKey]['label']
            ]
        ];
    }

    /**
     * Get cashier-specific sales reports (Strictly Manager/Owner Only).
     */
    public function getCashierSales(Request $request)
    {
        // Strict role validation: Only Manager or Owner
        $userRole = strtolower(trim($request->header('X-User-Role') ?: $request->query('role', '')));
        if (!in_array($userRole, ['owner', 'manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Only Store Managers and Owners can view cashier sales reports.'
            ], 403);
        }

        $range = $request->query('range', 'daily');
        $cashierFilterId = $request->query('cashier_id');

        switch ($range) {
            case 'weekly':
                $start = Carbon::today()->subDays(6)->startOfDay();
                $end = Carbon::today()->endOfDay();
                $periodTitle = 'Last 7 Days (Weekly)';
                break;
            case 'monthly':
                $start = Carbon::today()->subDays(29)->startOfDay();
                $end = Carbon::today()->endOfDay();
                $periodTitle = 'Last 30 Days (Monthly)';
                break;
            case 'all':
                $start = Carbon::createFromTimestamp(0);
                $end = Carbon::now()->addYear();
                $periodTitle = 'All-Time History';
                break;
            case 'daily':
            default:
                $range = 'daily';
                $start = Carbon::today()->startOfDay();
                $end = Carbon::today()->endOfDay();
                $periodTitle = "Today's Shift";
                break;
        }

        // Base query for orders in this date range
        $baseOrderQuery = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['pending', 'completed']);

        $totalStoreSales = (float) (clone $baseOrderQuery)->sum('total');
        $totalStoreOrders = (int) (clone $baseOrderQuery)->count();

        // Get aggregated stats by cashier
        $cashierStatsQuery = (clone $baseOrderQuery)
            ->select(
                'cashier_id',
                'cashier_name',
                DB::raw('COUNT(id) as orders_count'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw("SUM(CASE WHEN payment_method = 'cash' THEN total ELSE 0 END) as cash_sales"),
                DB::raw("SUM(CASE WHEN payment_method = 'gcash' THEN total ELSE 0 END) as gcash_sales"),
                DB::raw('MIN(created_at) as first_sale_at'),
                DB::raw('MAX(created_at) as last_sale_at')
            )
            ->groupBy('cashier_id', 'cashier_name');

        // Strictly exclude management/owner roles from cashier sales reports
        $excludedUserIds = \App\Models\User::whereIn('role', ['manager', 'owner', 'admin'])
            ->pluck('id')
            ->toArray();
        $excludedUserNames = \App\Models\User::whereIn('role', ['manager', 'owner', 'admin'])
            ->pluck('name')
            ->map(function ($n) {
                return strtolower(trim($n));
            })
            ->toArray();

        // If filtering specifically by a manager ID, return empty cashier list since manager is not a cashier
        if (!empty($cashierFilterId) && in_array($cashierFilterId, $excludedUserIds)) {
            $rosterUsers = \App\Models\User::where('role', 'cashier')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);

            return response()->json([
                'success' => true,
                'period' => $range,
                'period_title' => $periodTitle,
                'summary' => [
                    'total_store_sales' => (float) $totalStoreSales,
                    'total_store_orders' => $totalStoreOrders,
                    'total_cashier_sales' => 0.0,
                    'active_cashiers_count' => 0,
                    'avg_sales_per_cashier' => 0.0,
                    'top_cashier_name' => 'N/A',
                    'top_cashier_sales' => 0.0,
                ],
                'chart' => [
                    'labels' => [],
                    'values' => [],
                    'orders' => [],
                ],
                'cashiers' => [],
                'roster' => $rosterUsers->map(function ($u) {
                    return ['id' => $u->id, 'name' => $u->name, 'role' => ucfirst($u->role)];
                })
            ]);
        }

        if (!empty($cashierFilterId)) {
            $cashierStatsQuery->where('cashier_id', $cashierFilterId);
        }

        // Exclude orders with cashier_id belonging to managers/owners
        if (!empty($excludedUserIds)) {
            $cashierStatsQuery->where(function ($q) use ($excludedUserIds) {
                $q->whereNull('cashier_id')
                  ->orWhereNotIn('cashier_id', $excludedUserIds);
            });
        }
        // Also exclude orders created with manager names
        foreach ($excludedUserNames as $exName) {
            $cashierStatsQuery->whereRaw('LOWER(TRIM(COALESCE(cashier_name, ""))) != ?', [$exName]);
        }
        $cashierStatsQuery->whereRaw('LOWER(COALESCE(cashier_name, "")) NOT LIKE ?', ['%manager%']);

        $cashierResults = $cashierStatsQuery->get()->keyBy(function ($item) {
            return $item->cashier_id ?? ('name_' . ($item->cashier_name ?: 'unassigned'));
        });

        // Strictly fetch only staff with role 'cashier' from users table to display all roster cashiers
        $rosterUsers = \App\Models\User::where('role', 'cashier')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $cashiersList = [];
        $trackedIds = [];

        // 1. Process users from the cashier roster
        foreach ($rosterUsers as $user) {
            $trackedIds[] = $user->id;
            $stats = $cashierResults->get($user->id);

            $sales = $stats ? (float) $stats->total_sales : 0.0;
            $ordersCount = $stats ? (int) $stats->orders_count : 0;
            $cashSales = $stats ? (float) $stats->cash_sales : 0.0;
            $gcashSales = $stats ? (float) $stats->gcash_sales : 0.0;
            $aov = $ordersCount > 0 ? round($sales / $ordersCount, 2) : 0.0;
            $sharePercent = $totalStoreSales > 0 ? round(($sales / $totalStoreSales) * 100, 1) : 0.0;

            $cashiersList[] = [
                'cashier_id' => $user->id,
                'cashier_name' => $user->name,
                'email' => $user->email,
                'role' => 'Cashier',
                'total_sales' => $sales,
                'orders_count' => $ordersCount,
                'avg_order_value' => $aov,
                'cash_sales' => $cashSales,
                'gcash_sales' => $gcashSales,
                'share_percent' => $sharePercent,
                'first_sale_at' => $stats && $stats->first_sale_at ? Carbon::parse($stats->first_sale_at)->format('h:i A') : '—',
                'last_sale_at' => $stats && $stats->last_sale_at ? Carbon::parse($stats->last_sale_at)->format('h:i A') : '—',
            ];
        }

        // 2. Process any orders where cashier_id is null or not in current user roster (excluding managers)
        foreach ($cashierResults as $key => $stats) {
            if ($stats->cashier_id && in_array($stats->cashier_id, $trackedIds)) {
                continue;
            }
            if ($stats->cashier_id && in_array($stats->cashier_id, $excludedUserIds)) {
                continue;
            }
            if ($stats->cashier_name && (in_array(strtolower(trim($stats->cashier_name)), $excludedUserNames) || stripos($stats->cashier_name, 'manager') !== false)) {
                continue;
            }
            $sales = (float) $stats->total_sales;
            $ordersCount = (int) $stats->orders_count;
            $aov = $ordersCount > 0 ? round($sales / $ordersCount, 2) : 0.0;
            $sharePercent = $totalStoreSales > 0 ? round(($sales / $totalStoreSales) * 100, 1) : 0.0;

            $cashiersList[] = [
                'cashier_id' => $stats->cashier_id,
                'cashier_name' => $stats->cashier_name ?: 'Front Counter Staff',
                'email' => 'counter@earthbred.internal',
                'role' => 'Cashier',
                'total_sales' => $sales,
                'orders_count' => $ordersCount,
                'avg_order_value' => $aov,
                'cash_sales' => (float) $stats->cash_sales,
                'gcash_sales' => (float) $stats->gcash_sales,
                'share_percent' => $sharePercent,
                'first_sale_at' => $stats->first_sale_at ? Carbon::parse($stats->first_sale_at)->format('h:i A') : '—',
                'last_sale_at' => $stats->last_sale_at ? Carbon::parse($stats->last_sale_at)->format('h:i A') : '—',
            ];
        }

        // If filtering by specific cashier, filter the array
        if (!empty($cashierFilterId)) {
            $cashiersList = array_values(array_filter($cashiersList, function ($c) use ($cashierFilterId) {
                return (string) $c['cashier_id'] === (string) $cashierFilterId;
            }));
        }

        // Sort cashiers by total sales descending
        usort($cashiersList, function ($a, $b) {
            return $b['total_sales'] <=> $a['total_sales'];
        });

        // Calculate summary cards
        $activeCashiersCount = 0;
        $totalCashierSales = 0;
        foreach ($cashiersList as $c) {
            $totalCashierSales += $c['total_sales'];
            if ($c['orders_count'] > 0) {
                $activeCashiersCount++;
            }
        }

        $topCashier = !empty($cashiersList) && $cashiersList[0]['total_sales'] > 0 ? $cashiersList[0] : null;
        $avgSalesPerCashier = $activeCashiersCount > 0 ? round($totalCashierSales / $activeCashiersCount, 2) : 0.0;

        // Chart Data (Cashiers revenue comparison)
        $chartLabels = [];
        $chartValues = [];
        $chartOrders = [];
        foreach ($cashiersList as $c) {
            $chartLabels[] = $c['cashier_name'];
            $chartValues[] = round($c['total_sales'], 2);
            $chartOrders[] = $c['orders_count'];
        }

        return response()->json([
            'success' => true,
            'period' => $range,
            'period_title' => $periodTitle,
            'summary' => [
                'total_store_sales' => (float) $totalStoreSales,
                'total_store_orders' => $totalStoreOrders,
                'total_cashier_sales' => round($totalCashierSales, 2),
                'active_cashiers_count' => $activeCashiersCount,
                'avg_sales_per_cashier' => $avgSalesPerCashier,
                'top_cashier_name' => $topCashier ? $topCashier['cashier_name'] : 'N/A',
                'top_cashier_sales' => $topCashier ? $topCashier['total_sales'] : 0.0,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
                'orders' => $chartOrders,
            ],
            'cashiers' => $cashiersList,
            'roster' => $rosterUsers->map(function ($u) {
                return ['id' => $u->id, 'name' => $u->name, 'role' => ucfirst($u->role)];
            })
        ]);
    }

    /**
     * Get detailed transaction breakdown and top products sold by a specific cashier.
     */
    public function getCashierDetails(Request $request, $id)
    {
        $userRole = strtolower(trim($request->header('X-User-Role') ?: $request->query('role', '')));
        if (!in_array($userRole, ['owner', 'manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Only Store Managers and Owners can view cashier sales reports.'
            ], 403);
        }

        $range = $request->query('range', 'all');
        switch ($range) {
            case 'daily':
                $start = Carbon::today()->startOfDay();
                $end = Carbon::today()->endOfDay();
                break;
            case 'weekly':
                $start = Carbon::today()->subDays(6)->startOfDay();
                $end = Carbon::today()->endOfDay();
                break;
            case 'monthly':
                $start = Carbon::today()->subDays(29)->startOfDay();
                $end = Carbon::today()->endOfDay();
                break;
            case 'all':
            default:
                $start = Carbon::createFromTimestamp(0);
                $end = Carbon::now()->addYear();
                break;
        }

        $user = \App\Models\User::find($id);
        if ($user && in_array(strtolower($user->role), ['manager', 'owner', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'The selected staff member is a ' . ucfirst($user->role) . ', not a cashier.'
            ], 404);
        }
        $cashierName = $user ? $user->name : "Cashier #{$id}";

        // Orders by this cashier
        $ordersQuery = Order::with('items')
            ->where(function ($q) use ($id, $cashierName) {
                $q->where('cashier_id', $id)
                  ->orWhere('cashier_name', $cashierName);
            })
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['pending', 'completed'])
            ->orderBy('created_at', 'desc');

        $totalSales = (float) (clone $ordersQuery)->sum('total');
        $totalOrders = (int) (clone $ordersQuery)->count();
        $cashSales = (float) (clone $ordersQuery)->where('payment_method', 'cash')->sum('total');
        $gcashSales = (float) (clone $ordersQuery)->where('payment_method', 'gcash')->sum('total');

        $recentOrders = (clone $ordersQuery)->limit(20)->get()->map(function ($ord) {
            return [
                'id' => $ord->id,
                'total' => (float) $ord->total,
                'payment_method' => strtoupper($ord->payment_method),
                'status' => ucfirst($ord->status),
                'items_count' => $ord->items->sum('quantity'),
                'created_at' => $ord->created_at->format('M d, Y h:i A'),
                'items' => $ord->items->map(function ($it) {
                    return [
                        'name' => $it->product_name,
                        'qty' => $it->quantity,
                        'total' => (float) $it->item_total
                    ];
                })
            ];
        });

        // Top items sold by this cashier
        $topItems = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($id, $cashierName, $start, $end) {
                $query->where(function ($q) use ($id, $cashierName) {
                    $q->where('cashier_id', $id)
                      ->orWhere('cashier_name', $cashierName);
                })
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'cashier' => [
                'id' => $id,
                'name' => $cashierName,
                'email' => $user ? $user->email : '—',
                'role' => $user ? ucfirst($user->role) : 'Cashier',
            ],
            'summary' => [
                'total_sales' => $totalSales,
                'orders_count' => $totalOrders,
                'cash_sales' => $cashSales,
                'gcash_sales' => $gcashSales,
                'avg_order_value' => $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0,
            ],
            'top_items' => $topItems,
            'recent_orders' => $recentOrders
        ]);
    }
}
