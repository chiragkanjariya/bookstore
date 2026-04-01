<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShreeMarutiSeries;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarutiSeriesController extends Controller
{
    public function index(Request $request)
    {
        $query = ShreeMarutiSeries::query();

        if ($request->filled('series_id')) {
            $query->where('series_id', $request->series_id);
        }

        $seriesRecords = $query->orderBy('id', 'desc')->paginate(50);
        $seriesRecords->appends(request()->query());

        $notificationEmail = Setting::get('shree_maruti_notification_email', '');
        $notifyThreshold = Setting::get('shree_maruti_notify_threshold', '');

        // Fetch distinct series_ids for the filter dropdown
        $availableSeriesIds = ShreeMarutiSeries::select('series_id')->distinct()->orderBy('series_id', 'desc')->pluck('series_id');

        return view('admin.maruti-series.index', compact('seriesRecords', 'notificationEmail', 'notifyThreshold', 'availableSeriesIds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_number' => 'required|numeric',
            'end_number' => 'required|numeric',
        ]);

        $start = $request->start_number;
        $end = $request->end_number;

        $isGreater = false;
        if (function_exists('bccomp')) {
            $isGreater = bccomp($start, $end) > 0;
        } else {
            $isGreater = (strlen($start) > strlen($end)) || (strlen($start) === strlen($end) && $start > $end);
        }

        if ($isGreater) {
            return back()->with('error', 'Start number must be less than or equal to end number.');
        }

        $seriesId = ShreeMarutiSeries::max('series_id') + 1;
        $current = $start;
        $records = [];
        $addedCount = 0;

        DB::beginTransaction();
        try {
            $isLessOrEqual = true;
            if (function_exists('bccomp')) {
                $isLessOrEqual = bccomp($current, $end) <= 0;
            } else {
                if (strlen($current) < strlen($end)) {
                    $isLessOrEqual = true;
                } elseif (strlen($current) > strlen($end)) {
                    $isLessOrEqual = false;
                } else {
                    $isLessOrEqual = $current <= $end;
                }
            }

            while ($isLessOrEqual) {
                // Keep leading zeros if any
                $awb = str_pad($current, strlen($start), '0', STR_PAD_LEFT);
                
                // Avoid duplicates
                if (!ShreeMarutiSeries::where('awb_number', $awb)->exists()) {
                    $records[] = [
                        'series_id' => $seriesId,
                        'awb_number' => $awb,
                        'is_used' => false,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $addedCount++;
                }

                if (function_exists('bcadd')) {
                    $current = bcadd($current, '1');
                } else {
                    $current = (string) ($current + 1);
                }

                // Update condition for next iteration
                if (function_exists('bccomp')) {
                    $isLessOrEqual = bccomp($current, $end) <= 0;
                } else {
                    if (strlen($current) < strlen($end)) {
                        $isLessOrEqual = true;
                    } elseif (strlen($current) > strlen($end)) {
                        $isLessOrEqual = false;
                    } else {
                        $isLessOrEqual = $current <= $end;
                    }
                }

                if (count($records) >= 500) {
                    ShreeMarutiSeries::insert($records);
                    $records = [];
                }

                // Hard limit to avoid infinite loops or memory explosion
                if ($addedCount > 100000) {
                    throw new \Exception('Cannot generate more than 100,000 numbers in one go.');
                }
            }

            if (count($records) > 0) {
                ShreeMarutiSeries::insert($records);
            }

            DB::commit();
            return back()->with('success', "Batch generated successfully. Added {$addedCount} tracking numbers under Series ID: {$seriesId}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ShreeMaruti Series Generation Failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate series: ' . $e->getMessage());
        }
    }

    public function destroySeries(Request $request)
    {
        $request->validate([
            'series_id' => 'required|integer',
        ]);

        $seriesId = $request->series_id;

        // Check if any order is assigned in this series
        $hasAssignments = ShreeMarutiSeries::where('series_id', $seriesId)
            ->where('is_used', true)
            ->exists();

        if ($hasAssignments) {
            return back()->with('error', "Cannot delete Series ID {$seriesId} because one or more numbers have already been assigned to orders.");
        }

        DB::table('shree_maruti_series')->where('series_id', $seriesId)->delete();

        return back()->with('success', "Series ID {$seriesId} and its unassigned tracking numbers have been successfully deleted.");
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'shree_maruti_notification_email' => 'required|email',
            'shree_maruti_notify_threshold' => 'required|integer|min:1',
        ]);

        Setting::set('shree_maruti_notification_email', $request->shree_maruti_notification_email, 'string', 'courier', 'Shree Maruti Notification Email');
        Setting::set('shree_maruti_notify_threshold', $request->shree_maruti_notify_threshold, 'string', 'courier', 'Shree Maruti Notify Threshold');

        return back()->with('success', 'Notification settings updated successfully.');
    }
}
