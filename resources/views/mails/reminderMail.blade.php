<h2>Reminder Email</h2>

#Dear {{ $reminder->users->name }},
<p>
    You have a reminder for project "<p>{{$reminder->project->name}}</p>"
    on {{ date('d-m-Y', strtotime($reminder->remind_at)) }}
    at {{ date('h:i a', strtotime($reminder->remind_at)) }}
    with {{ $reminder->clients->name }}
</p>
<h4>Title: {{$reminder->title}}</h4>
<p>{{$reminder->description}}</p>
