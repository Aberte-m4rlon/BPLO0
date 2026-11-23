<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Mayor's Permit — {{ $permit->permit_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 14mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #123B63;
            margin: 0;
            padding: 0;
            background: #FFFFFF;
            font-size: 10.5px;
            line-height: 1.4;
        }

        .certificate-outer {
            border: 3px solid #155A9C;
            padding: 5px;
            background: #EFF8FF;
        }
        .certificate-inner {
            border: 1.5px solid #D99B26;
            background: #FFFFFF;
            padding: 18px 22px 16px;
        }

        /* Header with Emblem + Municipality + QR Code */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 11px; }
        .header-table td { vertical-align: middle; }
        .emblem-cell { width: 68px; }
        .emblem-circle {
            width: 54px;
            height: 54px;
            border-radius: 27px;
            background: #155A9C;
            border: 3px solid #D99B26;
            color: #FFFFFF;
            text-align: center;
            line-height: 41px;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .letterhead-cell { text-align: center; padding: 0 8px; }
        .republic { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1.4px; color: #526F89; margin: 0; }
        .province { font-size: 9.5px; color: #526F89; margin: 2px 0 0; }
        .municipality { font-size: 16px; font-weight: bold; color: #123B63; margin: 2px 0; text-transform: uppercase; letter-spacing: 0.6px; }
        .office { font-size: 9px; font-weight: bold; color: #945D06; text-transform: uppercase; letter-spacing: 0.8px; margin: 0; }

        .qr-cell { width: 104px; text-align: right; }
        .qr-frame {
            display: inline-block;
            border: 1.5px solid #64B5F6;
            background: #F5FAFF;
            padding: 5px;
            text-align: center;
        }
        .qr-frame img { width: 82px; height: 82px; display: block; }
        .qr-caption { font-size: 7px; font-weight: bold; color: #155A9C; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }

        /* Title Banner */
        .title-banner {
            background: #123B63;
            color: #FFFFFF;
            text-align: center;
            padding: 9px 14px;
            border-bottom: 4px solid #D99B26;
            margin-bottom: 12px;
        }
        .title-banner h1 {
            margin: 0;
            font-size: 19px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #FFFFFF;
        }
        .title-banner p {
            margin: 2px 0 0;
            font-size: 8.5px;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #FCE49C;
        }

        /* Permit Number & Status Bar */
        .meta-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background: #EFF8FF;
            border: 1px solid #B8DCF8;
            border-left: 4px solid #D99B26;
        }
        .meta-bar td { padding: 8px 12px; font-size: 10.5px; }
        .meta-label { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #526F89; display: block; margin-bottom: 1px; }
        .permit-no { font-size: 13.5px; font-weight: bold; color: #123B63; letter-spacing: 0.5px; }
        .status-cell { text-align: right; vertical-align: middle; }
        .status-pill {
            display: inline-block;
            padding: 4px 11px;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            border: 1px solid #86EFAC;
            background: #DCFCE7;
            color: #166534;
        }
        .status-pill.is-inactive {
            border-color: #FCA5A5;
            background: #FEE2E2;
            color: #991B1B;
        }

        /* Granted Recipient Box */
        .grant-box {
            border: 1px solid #B8DCF8;
            background: #F5FAFF;
            padding: 10px 14px;
            text-align: center;
            margin-bottom: 12px;
        }
        .grant-kicker {
            display: block;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.3px;
            color: #526F89;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .grant-biz {
            font-size: 15px;
            font-weight: bold;
            color: #0B3765;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .grant-note {
            text-align: center;
            font-size: 9.5px;
            color: #526F89;
            margin: 0 0 11px;
        }

        /* Details Table */
        table.details { width: 100%; border-collapse: collapse; border: 1px solid #B8DCF8; margin-bottom: 10px; }
        table.details td {
            padding: 7px 11px;
            font-size: 10.5px;
            vertical-align: top;
            border-bottom: 1px solid #D9EEFF;
        }
        table.details tr:last-child td { border-bottom: 0; }
        table.details td.label {
            width: 165px;
            font-weight: bold;
            color: #155A9C;
            background: #F5FAFF;
            border-right: 1px solid #D9EEFF;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.6px;
            padding-top: 8px;
        }
        table.details td.value { color: #123B63; background: #FFFFFF; }

        /* Validity Strip */
        .validity-bar { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .validity-bar td {
            width: 50%;
            padding: 7px 12px;
            border: 1px solid #B8DCF8;
            background: #F8FBFF;
        }
        .validity-bar td.valid-until-cell {
            background: #EFF8FF;
            border-left: 3px solid #D99B26;
        }
        .validity-label { display: block; font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #526F89; }
        .validity-value { font-size: 11.5px; font-weight: bold; color: #123B63; }

        /* Compliance Note & Signatories */
        .compliance {
            margin-top: 8px;
            padding: 8px 11px;
            background: #FFFBF0;
            border-left: 3px solid #D99B26;
            font-size: 9px;
            color: #526F89;
        }

        .signatures { width: 100%; border-collapse: collapse; margin-top: 26px; }
        .signatures td { width: 50%; text-align: center; padding: 0 14px; vertical-align: bottom; }
        .sig-line {
            border-top: 1.5px solid #123B63;
            padding-top: 4px;
            margin: 0 auto;
            width: 82%;
        }
        .sig-title { font-size: 9.5px; font-weight: bold; color: #123B63; text-transform: uppercase; }
        .sig-role { font-size: 8px; color: #526F89; margin-top: 1px; }

        /* Footer */
        .footer {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #D9EEFF;
            font-size: 8px;
            color: #526F89;
            text-align: center;
        }
        .footer a { color: #155A9C; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="certificate-outer">
        <div class="certificate-inner">
            <table class="header-table">
                <tr>
                    <td class="emblem-cell">
                        <div class="emblem-circle">BP</div>
                    </td>
                    <td class="letterhead-cell">
                        <p class="republic">Republic of the Philippines</p>
                        <p class="province">Province of {{ $province }}</p>
                        <p class="municipality">{{ $municipality }}</p>
                        <p class="office">Office of the Municipal Mayor · Business Permits &amp; Licensing Office</p>
                    </td>
                    <td class="qr-cell">
                        <div class="qr-frame">
                            <a href="{{ $verifyUrl }}"><img src="{{ $qrDataUri }}" alt="Verification QR"></a>
                            <div class="qr-caption">Scan to Verify</div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="title-banner">
                <h1>MAYOR'S PERMIT</h1>
                <p>Official Business Permit &amp; License to Operate</p>
            </div>

            <table class="meta-bar">
                <tr>
                    <td>
                        <span class="meta-label">Permit Number</span>
                        <span class="permit-no">{{ $permit->permit_number }}</span>
                    </td>
                    <td class="status-cell">
                        <span class="meta-label">Permit Status</span>
                        <span class="status-pill {{ strtoupper((string) $permit->status) === 'ACTIVE' ? '' : 'is-inactive' }}">
                            {{ strtoupper((string) $permit->status) }}
                        </span>
                    </td>
                </tr>
            </table>

            <div class="grant-box">
                <span class="grant-kicker">This permit is hereby granted to</span>
                <div class="grant-biz">{{ $permit->business_name }}</div>
            </div>

            <p class="grant-note">
                Having complied with all municipal requirements and paid the prescribed fees to operate within the jurisdiction of {{ $municipality }}, {{ $province }}.
            </p>

            <table class="details">
                <tr><td class="label">Proprietor / Owner</td><td class="value">{{ $permit->owner_name }}</td></tr>
                <tr><td class="label">Business Address</td><td class="value">{{ $permit->business_address }}</td></tr>
                <tr><td class="label">Nature of Business</td><td class="value">{{ $permit->business_nature }}</td></tr>
            </table>

            <table class="validity-bar">
                <tr>
                    <td>
                        <span class="validity-label">Date Issued</span>
                        <span class="validity-value">{{ $permit->issued_at?->format('F d, Y') }}</span>
                    </td>
                    <td class="valid-until-cell">
                        <span class="validity-label">Valid Until</span>
                        <span class="validity-value">{{ $permit->valid_until?->format('F d, Y') }}</span>
                    </td>
                </tr>
            </table>

            <div class="compliance">
                <strong>Notice:</strong> This permit must be displayed conspicuously at the business premises. Any alteration invalidates this document. Authenticity is verified exclusively via the QR code above.
            </div>

            <table class="signatures">
                <tr>
                    <td>
                        <div class="sig-line">
                            <div class="sig-title">BPLO Licensing Officer</div>
                            <div class="sig-role">Business Permits &amp; Licensing Office</div>
                        </div>
                    </td>
                    <td>
                        <div class="sig-line">
                            <div class="sig-title">Municipal Mayor</div>
                            <div class="sig-role">{{ $municipality }}, {{ $province }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="footer">
                Verify authenticity online at <a href="{{ $verifyUrl }}">{{ $verifyUrl }}</a>
            </div>
        </div>
    </div>
</body>
</html>
