<?php

namespace App\Console\Commands;

use App\Mail\ReminderMail;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reminders = Reminder::with('users')->where('date', Carbon::now()->toDateString())->where('status', 1)->get();

        foreach ($reminders as $reminder) {
            $reminder_at = new Carbon($reminder->reminder_at);
            $now = Carbon::now()->toTimeString();
            if ($reminder_at->diffInMinutes($now) == 0) {
                info('this is send');
                Mail::to($reminder->users->email)->send(new ReminderMail($reminder));
            }
        }
    }
}
