<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $progressTitle }}</title>
</head>
<body style="margin:0;padding:24px 12px;background:#EFF8FF;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#123B63;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;margin:0 auto;background:#FFFFFF;border:1px solid #B8DCF8;border-radius:16px;overflow:hidden;">
        <tr>
            <td style="background:#123B63;border-bottom:4px solid #D99B26;padding:22px 26px;color:#FFFFFF;">
                <div style="font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:#FCE49C;margin-bottom:4px;">
                    {{ $municipality }} · Business Permits &amp; Licensing Office
                </div>
                <h1 style="margin:0;font-size:21px;line-height:1.3;color:#FFFFFF;">{{ $progressTitle }}</h1>
            </td>
        </tr>
        <tr>
            <td style="padding:24px 26px;">
                <p style="margin:0 0 14px;font-size:15px;line-height:1.6;">
                    Hello <strong>{{ $application?->applicant_name ?? $applicant->name }}</strong>,
                </p>

                <div style="margin:0 0 18px;padding:14px 16px;background:#F5FAFF;border:1px solid #D9EEFF;border-left:4px solid #D99B26;border-radius:10px;font-size:14px;line-height:1.6;color:#123B63;">
                    {{ $progressBody }}
                </div>

                @if ($application)
                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin:0 0 20px;border:1px solid #D9EEFF;border-radius:10px;">
                        @if ($application->tracking_number)
                            <tr>
                                <td style="padding:9px 12px;border-bottom:1px solid #E5F2FC;background:#F5FAFF;font-size:12px;font-weight:700;color:#526F89;width:170px;">Tracking Number</td>
                                <td style="padding:9px 12px;border-bottom:1px solid #E5F2FC;font-size:13px;font-weight:700;color:#123B63;">{{ $application->tracking_number }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td style="padding:9px 12px;border-bottom:1px solid #E5F2FC;background:#F5FAFF;font-size:12px;font-weight:700;color:#526F89;">Business Name</td>
                            <td style="padding:9px 12px;border-bottom:1px solid #E5F2FC;font-size:13px;color:#123B63;">{{ $application->business_name }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 12px;{{ $application->permit ? 'border-bottom:1px solid #E5F2FC;' : '' }}background:#F5FAFF;font-size:12px;font-weight:700;color:#526F89;">Current Status</td>
                            <td style="padding:9px 12px;{{ $application->permit ? 'border-bottom:1px solid #E5F2FC;' : '' }}font-size:13px;font-weight:700;color:#155A9C;">{{ $statusLabel }}</td>
                        </tr>
                        @if ($application->permit)
                            <tr>
                                <td style="padding:9px 12px;border-bottom:1px solid #E5F2FC;background:#F5FAFF;font-size:12px;font-weight:700;color:#526F89;">Permit Number</td>
                                <td style="padding:9px 12px;border-bottom:1px solid #E5F2FC;font-size:13px;font-weight:700;color:#123B63;">{{ $application->permit->permit_number }}</td>
                            </tr>
                            <tr>
                                <td style="padding:9px 12px;background:#F5FAFF;font-size:12px;font-weight:700;color:#526F89;">Valid Until</td>
                                <td style="padding:9px 12px;font-size:13px;color:#123B63;">{{ $application->permit->valid_until?->format('F d, Y') }}</td>
                            </tr>
                        @endif
                    </table>
                @endif

                <p style="margin:0 0 18px;">
                    <a href="{{ $actionUrl }}" style="display:inline-block;padding:11px 20px;background:#2684D9;color:#FFFFFF;text-decoration:none;font-size:14px;font-weight:700;border-radius:10px;border:1px solid #155A9C;">
                        {{ $actionText }}
                    </a>
                </p>

                <p style="margin:0;font-size:12px;line-height:1.5;color:#526F89;">
                    If the button does not work, open this link in your browser:<br>
                    <a href="{{ $actionUrl }}" style="color:#155A9C;word-break:break-all;">{{ $actionUrl }}</a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:14px 26px;background:#F5FAFF;border-top:1px solid #D9EEFF;font-size:11.5px;color:#526F89;">
                This is an automated application progress email from the {{ $municipality }}, {{ $province }} BPLO Portal.
            </td>
        </tr>
    </table>
</body>
</html>
