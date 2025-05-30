<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitLevel;
use App\Models\Log;
use Illuminate\Http\Request;
use App\Http\Requests\HabitRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class HabitController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // 템플릿 습관 목록
        $templateHabits = Habit::where('is_template', 1)->get();

        // 현재 진행중인 것 제외 나의 습관 목록
        $habit = new Habit();
        $notProgressingHabits = $habit->selectNotProgressingHabits($user->id);

        return inertia('Habit/Index', [
            'templateHabits' => $templateHabits,
            'notProgressingHabits' => $notProgressingHabits,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia(
            'Habit/Create',
            [
                'type' => 'create'
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HabitRequest $request, ?Habit $habit)
    {
        try {
            // Habit/Habit_level 저장
            $user = auth()->user();
            $isAdmin = $user->is_admin;

            // 진행중인 습관 최대 3개 제한
            $logs = new Log();
            $currentLogs = $logs->selectCurrentLogsForUser($user->id);
            if ($currentLogs->count() >= 3) {
                return redirect()->route('home')->with('error', '3개 이상 습관을 진행할 수 없습니다.');
            }

            $habitModel = new Habit();
            // 새로운 회차 시작: habit, habit_levels 수정 후 logs, level_logs 생성
            if ($request['type'] == 'newRound') {
                $habitModel->startNewRound($request, $habit);
                // 성공 응답
                return redirect()->route('home')->with('success', '새로운 회차 시작!');
            } else {
                // 새 habits, habit_levels, logs, level_logs 한번에 초기 생성
                $habitData = $habitModel->createWithLevelsAndLogs([
                    'title' => $request['title'],
                    'emoji' => $request['emoji'],
                    'creator_id' => $user['id'],
                    'is_template' => $isAdmin,
                    'is_public' => $request['isPublic'] ?? false,
                    'levels' => $request['levels'],
                ]);
                // 성공 응답
                return redirect()->route('home')->with('success', '습관 만들기 완료!');
            }
        } catch (\Exception $e) {
            // 실패 응답
            return redirect()->back()->with('error', '저장 실패' . $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Habit $habit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Habit $habit, int $logId)
    {
        $this->authorize('update', $habit);

        // habitLevels
        $habitLevel = new HabitLevel();
        $habitLevels = $habitLevel->selectHabitLevelsGroupByLevel($habit->id);

        return inertia(
            'Habit/Create',
            [
                'habit' => $habit,
                'habitLevels' => $habitLevels,
                'type' => 'edit',
                'logId' => $logId,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HabitRequest $request, Habit $habit)
    {
        // habits, habit_levels, logs, level_logs 한번에 업데이트
        $habit->updateWithLevelsAndLogs([
            'title' => $request['title'],
            'emoji' => $request['emoji'],
            'is_public' => $request['isPublic'] ?? false,
            'levels' => $request['levels'],
            'removedLevelIds' => $request['removedLevelIds'],
            'logId' => $request['logId'],
        ], $habit);

        return redirect()->route('home', $request['logId'])->with('success', '습관 수정 완료!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Habit $habit)
    {
        //
    }

    /**
     * 습관 복사(템플릿) 이동
     */
    public function copy(Habit $habit)
    {
        $user = auth()->user();
        // 템플릿이 아닌 습관 id 혹은 생성자가 아니면 접근 불가
        if (!$habit->is_template && $habit->creator_id !== $user->id) {
            return redirect()->back();
        }
        // habitLevels
        $habitLevel = new HabitLevel();
        $habitLevels = $habitLevel->selectHabitLevelsGroupByLevel($habit->id);

        return inertia(
            'Habit/Create',
            [
                'habit' => $habit,
                'habitLevels' => $habitLevels,
                'type' => $habit->is_template ? 'copy' : 'newRound', // 템플릿 복사 혹은 새로운 회차 시작
            ]
        );
    }
}
