<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryOverviewController extends Controller
{
    public function data(Request $request)
    {
        $monthYear = $request->input('month_year', date('Y-m'));
        $selectedModels = $request->input('model', []);
        $selectedCustomers = $request->input('customer', []);
        $selectedStatusBalance = $request->input('status_balance', []);
        $selectedStatusUsage = $request->input('status_usage', []);

        $inCategories = DB::table('inv_m_transaction_category')->where('effect', 1)->pluck('code');
        $outCategories = DB::table('inv_m_transaction_category')->where('effect', -1)->pluck('code');

        // Helper for Status Filtering
        $applyStatusFilter = function($query, $statuses) {
            if (empty($statuses)) return;
            $query->where(function($q) use ($statuses) {
                 foreach ($statuses as $status) {
                     if ($status === 'Critical') {
                         $q->orWhere(function($w) {
                             $w->whereColumn('p.current_stock_qty', '<', 'p.min_stock')
                               ->where('p.min_stock', '>', 0)
                               ->where(function($sq) {
                                   $sq->where(function($inner) {
                                       $inner->where('ms.project_status', '!=', 'Regular')
                                             ->orWhereNull('ms.project_status');
                                   })
                                   ->where(function($inner) {
                                       $inner->whereNotIn('p.product_status', ['Oldstock OK', 'Oldstock NG'])
                                             ->orWhereNull('p.product_status');
                                   });
                               });
                         });
                     } elseif ($status === 'Over') {
                         $q->orWhere(function($w) {
                             $w->whereColumn('p.current_stock_qty', '>', DB::raw('p.min_stock * 3'))
                               ->where('p.min_stock', '>', 0);
                         });
                     } elseif ($status === 'Safe') {
                         $q->orWhere(function($w) {
                              $w->where(function($inner) {
                                  $inner->where(function($std) {
                                      $std->whereColumn('p.current_stock_qty', '>=', 'p.min_stock')
                                          ->whereColumn('p.current_stock_qty', '<=', DB::raw('p.min_stock * 3'));
                                  })
                                  ->orWhere(function($override) {
                                      $override->whereColumn('p.current_stock_qty', '<', 'p.min_stock')
                                               ->where(function($sq) {
                                                   $sq->where('ms.project_status', 'Regular')
                                                      ->orWhereIn('p.product_status', ['Oldstock OK', 'Oldstock NG']);
                                               });
                                  })
                                  ->orWhere('p.min_stock', '<=', 0)
                                  ->orWhereNull('p.min_stock');
                              });
                         });
                     }
                 }
            });
        };

        // 1. Stock Query
        $stockQuery = DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id')
            ->where('p.is_active', 1);

        if (!empty($selectedModels)) $stockQuery->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $stockQuery->whereIn('prod.customer_id', $selectedCustomers);
        $applyStatusFilter($stockQuery, $selectedStatusBalance);

        $pcsSql = $this->getPcsCalculationSql('p.current_stock_qty', 'p', 'u.name');
        $amountSql = $this->getAmountCalculationSql('p.current_stock_qty', 'p', 'u.name');
        
        $totalStockPcs = (clone $stockQuery)->selectRaw("SUM({$pcsSql}) as total")->value('total') ?? 0;
        $totalStockAmount = (clone $stockQuery)->selectRaw("SUM({$amountSql}) as total")->value('total') ?? 0;

        // 2. Transaction Query Base
        $recentTransQuery = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as p', 'p.id', '=', 't.product_detail_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id');

        $queryTrans = clone $recentTransQuery;
        if (!empty($selectedModels)) $queryTrans->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $queryTrans->whereIn('prod.customer_id', $selectedCustomers);
        if ($monthYear) $queryTrans->where('t.transaction_date', 'like', "$monthYear%");
        $applyStatusFilter($queryTrans, $selectedStatusBalance);

        // Stats (Item Part)
        $materialInCount = (clone $queryTrans)->whereIn('tc.code', $inCategories)->distinct()->count('p.id');
        $materialOutEventCount = (clone $queryTrans)->where('tc.code', 'OUT-EVENT')->distinct()->count('p.id');
        $materialOutPPCount = (clone $queryTrans)->where('tc.code', 'OUT-PP')->distinct()->count('p.id');
        $materialOutTrialCount = (clone $queryTrans)->where('tc.code', 'OUT-TRIAL')->distinct()->count('p.id');

        $stats = [
            'total_stock' => $totalStockPcs,
            'total_stock_value' => $totalStockAmount,
            'material_in' => $materialInCount,
            'out_event' => $materialOutEventCount,
            'out_pp' => $materialOutPPCount,
            'out_trial' => $materialOutTrialCount,
        ];

        // Stock Data for Bar Chart (Item Count per Status)
        $allProducts = $stockQuery->select(
            'm.name as model_name', 
            'c.code as customer_code', 
            'p.current_stock_qty', 
            'p.min_stock', 
            'p.product_status', 
            'ms.project_status',
            'p.pcs_per_unit',
            'p.weight_kg',
            'p.gross_coil',
            'u.name as unit_name'
        )->get();

        $stockDataGrouped = [];
        foreach ($allProducts as $prd) {
            $key = ($prd->model_name ?? 'N/A') . '|' . ($prd->customer_code ?? 'N/A');
            if (!isset($stockDataGrouped[$key])) {
                $stockDataGrouped[$key] = ['critical' => 0, 'warning' => 0, 'over' => 0, 'safe' => 0];
            }

            // Convert to PCS for accurate comparison
            $currentPcs = $this->calculatePcs(
                $prd->current_stock_qty, $prd->weight_kg, $prd->pcs_per_unit, $prd->unit_name, 
                0, 0, 0, 1, $prd->gross_coil
            );

            $status = $this->calculateStockStatus(
                $currentPcs, $prd->min_stock, $prd->project_status ?: $prd->product_status
            );

            if (isset($stockDataGrouped[$key][$status])) {
                $stockDataGrouped[$key][$status]++;
            }
        }

        // Usage by Model (Item Part) - Grouped for Stacked Chart
        $usageByModelData = (clone $queryTrans)
            ->whereIn('tc.code', $outCategories)
            ->select(
                DB::raw("m.name + '|' + c.code as label"), 
                'tc.code as category',
                DB::raw("COUNT(DISTINCT p.id) as total")
            )
            ->groupBy('m.name', 'c.code', 'tc.code')
            ->get();

        $usageByModel = [];
        $groupedUsage = [];
        foreach ($usageByModelData as $item) {
            if (!isset($groupedUsage[$item->label])) {
                $groupedUsage[$item->label] = ['OUT-EVENT' => 0, 'OUT-PP' => 0, 'OUT-TRIAL' => 0];
            }
            $groupedUsage[$item->label][$item->category] = $item->total;
        }

        foreach ($groupedUsage as $label => $counts) {
            $usageByModel[] = [
                'label' => $label,
                'event' => $counts['OUT-EVENT'],
                'pp' => $counts['OUT-PP'],
                'trial' => $counts['OUT-TRIAL']
            ];
        }

        // Trendline (Item Part) - Always 12 months
        $trendYear = substr($monthYear, 0, 4) ?: date('Y');
        $trendQuery = clone $recentTransQuery;
        if (!empty($selectedModels)) $trendQuery->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $trendQuery->whereIn('prod.customer_id', $selectedCustomers);
        
        $trendDataRaw = $trendQuery
            ->whereYear('t.transaction_date', $trendYear)
            ->select(
                DB::raw("MONTH(t.transaction_date) as month_num"), 
                'tc.code as category', 
                DB::raw("COUNT(DISTINCT p.id) as total")
            )
            ->groupBy(DB::raw("MONTH(t.transaction_date)"), 'tc.code')
            ->get();

        $trendlineByCat = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $categories = ['IN', 'OUT-EVENT', 'OUT-PP', 'OUT-TRIAL'];
        
        foreach ($months as $mIdx => $mName) {
            $monthNum = $mIdx + 1;
            foreach ($categories as $cat) {
                $found = $trendDataRaw->where('month_num', $monthNum)->where('category', $cat)->first();
                $trendlineByCat[] = [
                    'transaction_date' => $mName,
                    'category' => $cat,
                    'total' => $found ? $found->total : 0
                ];
            }
        }

        // Usage Status by Maker (Supplier)
        $makerUsageQuery = (clone $queryTrans)
            ->join('inv_m_supplier as s', 's.id', '=', 't.supplier_id')
            ->join('inv_m_rank as r', 'r.id', '=', 'p.rank_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'p.revision_id')
            ->where('tc.code', 'OUT-TRIAL')
            ->select([
                's.code as maker',
                'prod.part_no',
                'rev.code as revision',
                'p.id as product_id',
                'r.code as rank_code',
                'r.process_type',
                'r.limit_value',
                'p.unit_per_car',
                'p.pcs_per_unit',
                'p.gross_coil',
                'u.name as unit_name',
                DB::raw("SUM(t.qty) as usage_qty")
            ])
            ->groupBy('s.code', 'prod.part_no', 'rev.code', 'p.id', 'r.code', 'r.process_type', 'r.limit_value', 'p.unit_per_car', 'p.pcs_per_unit', 'p.gross_coil', 'u.name')
            ->get();

        $makerData = [];
        $usageTable = [];

        foreach ($makerUsageQuery as $item) {
            $limit = $this->calculateAdjustedRank($item->process_type, $item->limit_value, $item->unit_per_car, $item->pcs_per_unit);
            $usagePcs = $this->calculatePcs($item->usage_qty, 0, $item->pcs_per_unit, $item->unit_name, 0, 0, 0, 1, $item->gross_coil);
            $gap = $limit - $usagePcs;
            
            $statusRaw = ($gap < 0) ? 'Loss' : (($gap < 50) ? 'Near Loss' : 'On Budget');
            
            // Apply Status Usage Filter
            if (!empty($selectedStatusUsage) && !in_array($statusRaw, $selectedStatusUsage)) {
                continue;
            }

            // For Chart
            $statusKey = strtolower(str_replace(' ', '_', $statusRaw));
            $maker = $item->maker ?: 'Unknown';
            if (!isset($makerData[$maker])) $makerData[$maker] = ['on_budget' => 0, 'near_loss' => 0, 'loss' => 0];
            $makerData[$maker][$statusKey]++;

            // For Table
            $usageTable[] = [
                'part_no' => $item->part_no,
                'revision' => $item->revision,
                'supplier_name' => $item->maker,
                'rank_display' => ($item->rank_code ?? '-') . ' ' . number_format($limit),
                'out_trial' => $usagePcs,
                'gap' => $gap,
                'status' => $statusRaw
            ];
        }

        $usageByMaker = [];
        foreach ($makerData as $maker => $counts) {
            $usageByMaker[] = ['maker' => $maker, 'on_budget' => $counts['on_budget'], 'near_loss' => $counts['near_loss'], 'loss' => $counts['loss']];
        }

        // Tables
        $balanceStatusTable = (clone $stockQuery)
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'p.revision_id')
            ->select(
                'prod.part_no', 'r.code as revision', 'c.code as customer_code', 
                'm.name as model_name', 'p.current_stock_qty', 'p.min_stock',
                'p.pcs_per_unit', 'p.weight_kg', 'p.gross_coil', 'u.name as unit_name',
                'ms.project_status', 'p.product_status'
            )
            ->limit(10)
            ->get()
            ->map(function ($item) {
                 $currentPcs = $this->calculatePcs(
                    $item->current_stock_qty, $item->weight_kg, $item->pcs_per_unit, $item->unit_name, 
                    0, 0, 0, 1, $item->gross_coil
                 );
                 
                 $status = $this->calculateStockStatus(
                    $currentPcs, $item->min_stock, $item->project_status ?: $item->product_status
                 );

                 $item->status = ucfirst($status);
                 return $item;
            });

        $usageStatusTable = (clone $balanceStatusTable);

        $transactionHistory = (clone $recentTransQuery)
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'p.revision_id')
            ->select('prod.part_no', 'r.code as revision', 't.qty', 'p.pcs_per_unit', 'p.weight_kg', 'p.gross_coil', 'tc.code as category', 't.transaction_date', 't.created_at', 'u.name as unit_name', 'm.name as model_name', 'c.code as customer_code')
            ->orderByDesc('t.transaction_date')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->qty_pcs = $this->calculatePcs($item->qty, $item->weight_kg, $item->pcs_per_unit, $item->unit_name, 0, 0, 0, 1, $item->gross_coil);
                return $item;
            });

        $initialModels = [];
        if (!empty($selectedModels)) {
            $initialModels = DB::table('models')->whereIn('id', $selectedModels)->select('id', 'name')->get();
        }

        $initialCustomers = [];
        if (!empty($selectedCustomers)) {
            $initialCustomers = DB::table('customers')->whereIn('id', $selectedCustomers)->select('id', 'code', 'name')->get();
        }

        $responseData = [
            'stats' => $stats,
            'charts' => [
                'stock_grouped' => $stockDataGrouped,
                'usage_model' => $usageByModel,
                'trendline' => $trendlineByCat,
                'maker' => $usageByMaker
            ],
            'tables' => [
                'balance' => $balanceStatusTable,
                'usage' => $usageTable,
                'history' => $transactionHistory
            ],
            'filters' => [
                'initial_models' => $initialModels,
                'initial_customers' => $initialCustomers,
                'selected_models' => $selectedModels,
                'selected_customers' => $selectedCustomers,
                'selected_status_balance' => $selectedStatusBalance,
                'selected_status_usage' => $selectedStatusUsage,
                'month_year' => $monthYear
            ]
        ];

        return response()->json($responseData);
    }

    private function getPcsCalculationSql($qtyColumn = 'p.current_stock_qty', $tableAlias = 'p', $unitNameColumn = 'u.name')
    {
        $alias = $tableAlias ? $tableAlias . '.' : '';
        $unitCheck = $unitNameColumn ?: "(SELECT name FROM inv_m_unit WHERE id = {$alias}unit_id)";
        
        return "
            CASE 
                WHEN LOWER({$unitCheck}) LIKE '%coil%' 
                     AND ISNULL({$alias}gross_coil, 0) > 0 
                THEN ({$qtyColumn} / {$alias}gross_coil) * COALESCE({$alias}pcs_per_unit, 1) 
                ELSE ({$qtyColumn}) * COALESCE({$alias}pcs_per_unit, 1) 
            END
        ";
    }

    private function getAmountCalculationSql($qtyColumn = 'p.current_stock_qty', $tableAlias = 'p', $unitNameColumn = 'u.name')
    {
        $alias = $tableAlias ? $tableAlias . '.' : '';
        $unitCheck = $unitNameColumn ?: "(SELECT name FROM inv_m_unit WHERE id = {$alias}unit_id)";
        
        return "
            CASE 
                WHEN LOWER({$unitCheck}) LIKE '%coil%' 
                THEN ({$qtyColumn} * ISNULL({$alias}material_price, 0)) 
                ELSE ({$qtyColumn} * COALESCE({$alias}pcs_per_unit, 1) * ISNULL({$alias}weight_kg, 0) * ISNULL({$alias}material_price, 0)) 
            END
        ";
    }

    private function calculatePcs($qty, $weightKg, $pcsPerUnit, $unitName, $topMm = 0, $endMm = 0, $pitch = 0, $pcsPerPitch = 1, $grossCoil = 0)
    {
        $qty = (float)$qty;
        $grossCoil = (float)$grossCoil;
        $pitch = (float)$pitch;
        $weightKg = (float)$weightKg;
        $unitName = strtolower($unitName ?? '');

        if (strpos($unitName, 'coil') === false || $grossCoil <= 0 || $weightKg <= 0) {
            return (int) floor($qty * (float)($pcsPerUnit ?: 1));
        }

        if ($pitch <= 0) {
            return (int) floor(($qty / $grossCoil) * (float)($pcsPerUnit ?: 1));
        }

        $weightPerMm = $weightKg / $pitch;
        $scrapKg = ((float)$topMm + (float)$endMm) * $weightPerMm;
        $yieldRatio = max(0, ($grossCoil - $scrapKg) / $grossCoil);
        $netQty = $qty * $yieldRatio;
        
        return (int) (floor($netQty / $weightKg) * (float)($pcsPerPitch ?: 1));
    }

    private function calculateStockStatus($currentPcs, $minStock, $projectStatus = null)
    {
        $minStock = (float)$minStock;
        $currentPcs = (float)$currentPcs;

        if ($minStock <= 0) return 'safe';

        $maxStock = $minStock * 3;

        if ($currentPcs > $maxStock) return 'over';
        
        $safeStatuses = ['Regular', 'Oldstock OK', 'Oldstock NG'];
        if ($projectStatus && in_array($projectStatus, $safeStatuses)) {
            return 'safe';
        }

        if ($currentPcs < ($minStock - 30)) return 'critical';
        if ($currentPcs < $minStock) return 'warning';

        return 'safe';
    }

    private function calculateAdjustedRank($processType, $limitValue, $unitPerCar, $pcsPerUnit)
    {
        $limitValue = (float)$limitValue;
        $unitPerCar = (float)($unitPerCar ?: 1);
        $pcsPerUnit = (float)($pcsPerUnit ?: 1);
        if ($processType === 'Draw' || $processType === 'Blank') {
            return $limitValue * $unitPerCar;
        } elseif ($processType === 'Full Progressive') {
            return $limitValue * $unitPerCar * $pcsPerUnit;
        }
        return $limitValue;
    }

    public function drilldown(Request $request)
    {
        $chartType  = $request->input('chart');
        $label      = $request->input('label');
        $monthYear  = $request->input('month_year', date('Y-m'));
        $statusFilter = $request->input('status');
        $search     = $request->input('search');
        $pageSize   = (int)$request->input('pageSize', 10);
        $page       = (int)$request->input('page', 1);
        $offset     = ($page - 1) * $pageSize;

        $outCategories = \App\Models\InventoryModel\Material\TransactionCategory::where('effect', -1)->pluck('code')->toArray();
        if (empty($outCategories)) {
            $outCategories = ['OUT-EVENT', 'OUT-PP', 'OUT-TRIAL', 'OUT-NG', 'OUT-SCRAP', 'OUT-ADJ', 'OUT-OTHER'];
        }

        $baseProduct = DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'p.revision_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id')
            ->where('p.is_active', 1);

        $baseTrans = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as p', 'p.id', '=', 't.product_detail_id')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'p.revision_id')
            ->leftJoin('inv_m_supplier as s', 's.id', '=', 't.supplier_id');

        $result = [];
        $total = 0;
        $title = 'Detail Explorer';

        if ($chartType === 'stock') {
            $parts = explode('|', $label);
            $modelName = $parts[0] ?? '';
            $custCode  = $parts[1] ?? '';
            $title = "Stock Detail — {$modelName} / {$custCode}";

            $query = (clone $baseProduct)
                ->where(DB::raw("ISNULL(m.name, 'N/A')"), $modelName)
                ->where(DB::raw("ISNULL(c.code, 'N/A')"), $custCode);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('prod.part_no', 'like', "%{$search}%");
                });
            }

            $items = $query->select(
                'prod.part_no', 'rev.code as revision',
                'p.current_stock_qty', 'p.min_stock',
                'p.pcs_per_unit', 'p.weight_kg', 'p.gross_coil',
                'u.name as unit_name', 'ms.project_status', 'p.product_status'
            )->get();

            $filteredItems = collect($items)->map(function($item) {
                $currentPcs = $this->calculatePcs(
                    $item->current_stock_qty, $item->weight_kg, $item->pcs_per_unit, $item->unit_name,
                    0, 0, 0, 1, $item->gross_coil
                );
                $status = $this->calculateStockStatus(
                    $currentPcs, $item->min_stock, $item->project_status ?: $item->product_status
                );
                $item->calc_stock = $currentPcs;
                $item->calc_status = ucfirst($status);
                return $item;
            });

            if ($statusFilter && strtolower($statusFilter) !== 'all') {
                $filteredItems = $filteredItems->filter(function($item) use ($statusFilter) {
                    return strtolower($item->calc_status) === strtolower($statusFilter);
                });
            }

            $total = $filteredItems->count();
            $pagedItems = $filteredItems->slice($offset, $pageSize)->values();

            foreach ($pagedItems as $row) {
                $result[] = [
                    'part_no' => $row->part_no . ($row->revision ? ' - ' . $row->revision : ''),
                    'stock' => number_format($row->calc_stock),
                    'min_stock' => number_format($row->min_stock),
                    'unit' => $row->unit_name ?: '-',
                    'status' => $row->calc_status,
                ];
            }

        } elseif ($chartType === 'usage_model') {
            $parts = explode('|', $label);
            $modelName = $parts[0] ?? '';
            $custCode  = $parts[1] ?? '';
            $title = "Usage Detail — {$modelName} / {$custCode}";

            $query = (clone $baseTrans)
                ->where('t.transaction_date', 'like', "{$monthYear}%")
                ->whereIn('tc.code', $outCategories)
                ->where(DB::raw("ISNULL(m.name, 'N/A')"), $modelName)
                ->where(DB::raw("ISNULL(c.code, 'N/A')"), $custCode);

            if ($statusFilter && strtolower($statusFilter) !== 'all') {
                $query->where('tc.code', $statusFilter);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('prod.part_no', 'like', "%{$search}%");
                });
            }

            $total = (clone $query)->distinct()->count(DB::raw("CONCAT(prod.part_no, ISNULL(rev.code,''), tc.code, CAST(t.transaction_date AS NVARCHAR))"));
            
            $items = $query->select(
                    'prod.part_no', 'rev.code as revision',
                    'tc.code as category',
                    DB::raw('SUM(t.qty * ISNULL(p.pcs_per_unit, 1)) as qty_pcs'),
                    't.transaction_date'
                )
                ->groupBy('prod.part_no', 'rev.code', 'tc.code', 't.transaction_date')
                ->orderBy('t.transaction_date', 'desc')
                ->offset($offset)->limit($pageSize)
                ->get();

            foreach ($items as $row) {
                $result[] = [
                    'part_no'   => $row->part_no . ($row->revision ? ' - ' . $row->revision : ''),
                    'category'  => $row->category,
                    'qty_pcs'   => number_format($row->qty_pcs),
                    'date'      => $row->transaction_date,
                ];
            }

        }

        return response()->json([
            'title'   => $title,
            'total'   => $total,
            'results' => $result
        ]);
    }
}
