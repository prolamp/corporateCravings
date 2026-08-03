Contact inquiry from {{ $inquiry['name'] }}

Email: {{ $inquiry['email'] }}
@if(! empty($inquiry['phone']))
Phone: {{ $inquiry['phone'] }}
@endif
Subject: {{ $inquiry['subject'] }}

Message:
{{ $inquiry['message'] }}
