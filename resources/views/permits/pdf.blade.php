<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Mayor's Permit — {{ $permit->permit_number }}</title>
    <style>
        @page { margin: 22px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #123B63;
            margin: 0;
            padding: 0;
            background: #FFFFFF;
            font-size: 11px;
            line-height: 1.45;
        }

        .certificate-outer {
            border: 3px solid #155A9C;
            padding: 6px;
            background: #EFF8FF;
        }
        .certificate-inner {
            border: 1.5px solid #D99B26;
            background: #FFFFFF;
            padding: 24px 26px 20px;
        }

        /* Header with Emblem + Municipality + QR Code */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .header-table td { vertical-align: middle; }
        .emblem-cell { width: 72px; }
        .emblem-circle {
            width: 58px;
            height: 58px;
            border-radius: 29px;
            background: #155A9C;
            border: 3px solid #D99B26;
            color: #FFFFFF;
            text-align: center;
            line-height: 44px;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .letterhead-cell { text-align: center; padding: 0 10px; }
        .republic { font-size: 9px; text-transform: uppercase; letter-spacing: 1.4px; color: #526F89; margin: 0; }
        .province { font-size: 10px; color: #526F89; margin: 2px 0 0; }
        .municipality { font-size: 17px; font-weight: bold; color: #123B63; margin: 3px 0 2px; text-transform: uppercase; letter-spacing: 0.6px; }
        .office { font-size: 10px; font-weight: bold; color: #945D06; text-transform: uppercase; letter-spacing: 1px; margin: 0; }

        .qr-cell { width: 110px; text-align: right; }
        .qr-frame {
            display: inline-block;
            border: 1.5px solid #64B5F6;
            background: #F5FAFF;
            padding: 6px;
            text-align: center;
        }
        .qr-frame img { width: 88px; height: 88px; display: block; }
        .qr-caption { font-size: 7.5px; font-weight: bold; color: #155A9C; text-transform: uppercase; letter-spacing: 0.6px; margin-top: 3px; }

        /* Title Banner */
        .title-banner {
            background: #123B63;
            color: #FFFFFF;
            text-align: center;
            padding: 11px 14px;
            border-bottom: 4px solid #D99B26;
            margin-bottom: 14px;
        }
        .title-banner h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #FFFFFF;
        }
        .title-banner p {
            margin: 3px 0 0;
            font-size: 9px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #FCE49C;
        }

        /* Permit Number & Status Bar */
        .meta-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            background: #EFF8FF;
            border: 1px solid #B8DCF8;
            border-left: 4px solid #D99B26;
        }
        .meta-bar td { padding: 9px 12px; font-size: 11px; }
        .meta-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1px; color: #526F89; display: block; margin-bottom: 2px; }
        .permit-no { font-size: 14px; font-weight: bold; color: #123B63; letter-spacing: 0.5px; }
        .status-cell { text-align: right; vertical-align: middle; }
        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            font-size: 9.5px;
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

        .grant-note {
            text-align: center;
            font-size: 10px;
            color: #526F89;
            margin: 0 0 14px;
        }

        /* Details Table */
        table.details { width: 100%; border-collapse: collapse; border: 1px solid #B8DCF8; }
        table.details td {
            padding: 9px 12px;
            font-size: 11px;
            vertical-align: top;
            border-bottom: 1px solid #D9EEFF;
        }
        table.details tr:last-child td { border-bottom: 0; }
        table.details td.label {
            width: 175px;
            font-weight: bold;
            color: #155A9C;
            background: #F5FAFF;
            border-right: 1px solid #D9EEFF;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.6px;
            padding-top: 11px;
        }
        table.details td.value { color: #123B63; background: #FFFFFF; }
        table.details td.value-strong { font-size: 13px; font-weight: bold; color: #0D2B4A; }

        /* Compliance Note & Signatories */
        .compliance {
            margin-top: 14px;
            padding: 9px 12px;
            background: #F5FAFF;
            border-left: 3px solid #2684D9;
            font-size: 9.5px;
            color: #526F89;
        }

        .signatures { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .signatures td { width: 50%; text-align: center; padding: 0 16px; vertical-align: bottom; }
        .sig-line {
            border-top: 1.5px solid #123B63;
            padding-top: 5px;
            margin: 0 auto;
            width: 82%;
        }
        .sig-title { font-size: 10px; font-weight: bold; color: #123B63; text-transform: uppercase; }
        .sig-role { font-size: 8.5px; color: #526F89; margin-top: 2px; }

        /* Footer */
        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #D9EEFF;
            font-size: 8.5px;
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
                        <p class="office">Business Permits and Licensing Office</p>
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

            <p class="grant-note">
                This certifies that the business establishment named below has complied with all municipal requirements and paid the prescribed fees to operate within the jurisdiction of {{ $municipality }}, {{ $province }}.
            </p>

            <table class="details">
                <tr><td class="label">Business Name</td><td class="value value-strong">{{ $permit->business_name }}</td></tr>
                <tr><td class="label">Proprietor / Owner</td><td class="value">{{ $permit->owner_name }}</td></tr>
                <tr><td class="label">Business Address</td><td class="value">{{ $permit->business_address }}</td></tr>
                <tr><td class="label">Nature of Business</td><td class="value">{{ $permit->business_nature }}</td></tr>
                <tr><td class="label">Date Issued</td><td class="value">{{ $permit->issued_at?->format('F d, Y') }}</td></tr>
                <tr><td class="label">Valid Until</td><td class="value"><strong>{{ $permit->valid_until?->format('F d, Y') }}</strong></td></tr>
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
