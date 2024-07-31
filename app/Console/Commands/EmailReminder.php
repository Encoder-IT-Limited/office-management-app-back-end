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
    protected $description = 'Send email reminder to users';

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
     * @return void
     */
    public function handle(): void
    {
        $reminders = Reminder::with('users', 'project', 'clients')
            ->whereDate('remind_at', Carbon::today()->toDateString())
            ->where('message', 1)
            ->where('status', 0)
            ->get();

        foreach ($reminders as $reminder) {
//            info('Sending email to ' . $reminder->users->email);
            Mail::to($reminder->users->email)->send(new ReminderMail($reminder));
//            Mail::to('iamtestuser222@gmail.com')->send(new ReminderMail($reminder));
        }
    }
}
