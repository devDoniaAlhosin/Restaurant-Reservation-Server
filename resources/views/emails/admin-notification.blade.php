<x-mail::message>
    # New Feedback Received

    You have received a new feedback message from {{ $contact->name }}.

    **Subject:** {{ $contact->subject }}

    **Message:**
    {{ $contact->message }}

    You can reply to this message at: {{ $contact->email }}

    Thanks,

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
