<h2>Reminder Email</h2>

<h6>Dear {{ $reminder->users->name }},</h6>
<p>You have a meeting at {{ date('h:i a', strtotime($reminder->time)) }} with {{ $reminder->clients->name }}</p>
