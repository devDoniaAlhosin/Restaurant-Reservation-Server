<x-mail::message>
    # Thank You for Your Feedback

    Dear {{ $contact->name }},

    Thank you for reaching out to BistroBliss! We have received your feedback regarding:

    Subject: {{ $contact->subject }}

    We value your input and will get back to you shortly if necessary.

    Thanks again for contacting us!

    Warm regards,
{{ config('app.name') }}
</x-mail::message>
