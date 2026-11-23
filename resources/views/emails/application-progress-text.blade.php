{{ $municipality }} — Business Permits & Licensing Office
============================================================

{{ $progressTitle }}

Hello {{ $application?->applicant_name ?? $applicant->name }},

{{ $progressBody }}

@if ($application)
@if ($application->tracking_number)Tracking Number: {{ $application->tracking_number }}
@endif
Business Name: {{ $application->business_name }}
Current Status: {{ $statusLabel }}
@if ($application->permit)
Permit Number: {{ $application->permit->permit_number }}
Valid Until: {{ $application->permit->valid_until?->format('F d, Y') }}
@endif
@endif

{{ $actionText }}: {{ $actionUrl }}

--
Automated progress notification from {{ $municipality }}, {{ $province }} BPLO Portal.
