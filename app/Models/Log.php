<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Log extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function habit()
    {
        return $this->belongsTo(Habit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function levelLogs()
    {
        return $this->hasMany(LevelLog::class);
    }

    public function addLevelLogs(array $levels, object $today): array
    {
        $habitGoalDays = 20;
        $models = [];
        for ($i = 0; $i < $habitGoalDays; $i++) {
            foreach ($levels as $habit_level_item) {
                $models[] = new LevelLog([
                    'habit_level_id' => data_get($habit_level_item, 'id'),
                    'content' => data_get($habit_level_item, 'content'),
                    'level' => data_get($habit_level_item, 'level'),
                    'seq' => data_get($habit_level_item, 'seq'),
                    'log_date' => $today->copy()->addDays($i),
                ]);
            }
        }
        $this->levelLogs()->saveMany($models);
        return $models;
    }

    /**
     * log 저장 후 level_log 저장
     * @param array $data   1. habit
     *                      2. levels
     * @return int logId 저장한 로그 id
     */
    public function addLogWithLevelLogs(array $data)
    {
        // 1. log 저장
        $habit = $data['habit'];
        $today = today();
        $log = new self;
        $log->title = $habit->title;
        $log->emoji = $habit->emoji;
        $log->creator_id = $habit->creator_id;
        $log->habit_id = $habit->id;
        $log->start_date = $today;
        $log->end_date = $today->copy()->addDays(19);
        $log->round = $this->checkLastRound($habit->id) + 1;
        $log->save();

        // 2. levelLog 저장
        $log->addLevelLogs($data['levels'], $today);

        return $log->id;
    }

    /**
     * 습관 진행회차 확인
     * 가장 최근 회차 정보를 가져옴
     */
    public function checkLastRound(int $habitId): int
    {
        $latestRound = Log::where('habit_id', $habitId)
            ->orderByDesc('round')
            ->value('round');

        return $latestRound ?? 0;
    }

    public function selectCurrentLogsForUser(int $userId): object
    {
        $logs = Log::where('creator_id', $userId)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        return $logs;
    }
}
