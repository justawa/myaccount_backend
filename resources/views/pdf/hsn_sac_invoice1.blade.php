<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Invoice </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        p {
            margin: 5px 0;
        }

        .mr0 {
            margin: 0;
        }

        .mr-top {
            margin-top: 10px;
        }

        .mr-bottom {
            margin-bottom: 10px;
        }

        .main {
            width: 100%;
            float: left;
            font-family: sans-serif;
            font-size: 12px;
        }

        h3 {
            font-size: 20px;
        }

        .colom-4 {
            width: 33%;
            float: left;
        }

        .table {
            width: 100%;
            border: 1px solid #ddd;
            font-family: sans-serif;
            font-size: 12px;
            border-collapse: collapse;
        }

        .table-condensed>tbody>tr>td,
        .table-condensed>tbody>tr>th,
        .table-condensed>tfoot>tr>td,
        .table-condensed>tfoot>tr>th,
        .table-condensed>thead>tr>td,
        .table-condensed>thead>tr>th {
            padding: 5px 10px;
        }

        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            border: 1px solid #ddd;
        }

        .table>thead>tr>th {
            padding: 10px;
        }

        #page-wrap {
            width: 800px;
            margin: 0 auto;
        }

        .panel-body {
            padding: 0px;
        }

        .height {
            min-height: 200px;
        }

        .icon {
            font-size: 47px;
            color: #5CB85C;
        }

        .iconbig {
            font-size: 77px;
            color: #5CB85C;
        }

        .table>tbody>tr>.emptyrow {
            border-top: none;
        }

        .table>thead>tr>.emptyrow {
            border-bottom: none;
        }

        .table>tbody>tr>.highrow {
            border-top: 3px solid;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .buyer-sign {
            text-align: right;
            margin-top: 50px;
        }

        .term {
            list-style: decimal;
            padding-left: 15px;
        }

        .term li {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div id="page-wrap">
        <div class="main mr-bottom">
            <div class="colom-4 text-left">GST No: <span class=""></span></div>
            <div class="colom-4 text-center">
                <h3 class="mr0">TAX INVOICE</h3>
            </div>
            <div class="colom-4 text-right">Contact No: <span class=""> njftghmf</span></div>
        </div>
        <div class="main">
            <div class="colom-4 text-left">LOGO <span class=""></span></div>
            <div class="colom-4 text-left">
                <p>Co. Name<span></span></p>
                <p>Address<span></span></p>
            </div>
            <div class="colom-4 text-right">Email: <span class=""> njftghmf</span></div>
        </div>
        <!-- Simple Invoice - START -->
        <table class="table table-condensed" cellspacing="0">
            <thead>
                <tr>
                    <th colspan="4"><strong>Bill To</strong></th>
                    <th colspan="4"><strong>Ship To</strong></th>
                    <th colspan="2"><strong>Invoice No</strong></th>
                    <th colspan="2"><strong>2</strong></th>
                </tr>
                <tr>
                    <td rowspan="10" colspan="4" style="vertical-align: text-top;">
                        <p>Samsung Galaxy S5</p>
                        <p>Samsung Galaxy S5</p>
                    </td>
                    <td rowspan="10" colspan="4" style="vertical-align: text-top;">$900</td>
                    <td colspan="2">Date </td>
                    <td colspan="2">12 April 2019</td>
                </tr>
                <tr>
                    <td colspan="2">Regrance Name</td>
                    <td colspan="2">2</td>
                </tr>
                <tr>
                    <td colspan="2">No</td>
                    <td colspan="2">2</td>
                </tr>
                <tr>
                    <td colspan="2">E-way Bill No</td>
                    <td colspan="2">2</td>
                </tr>
                <tr>
                    <td colspan="2">E-way Bill No</td>
                    <td colspan="2">2</td>
                </tr>
                <tr>
                    <td colspan="2">Transportation</td>
                    <td colspan="2">2</td>
                </tr>
                <tr>
                    <td colspan="2">Vehicle No</td>
                    <td colspan="2">2</td>
                </tr>
                <tr>
                    <td colspan="2">GR No</td>
                    <td colspan="2">2</td>
                </tr>
                <tr>
                    <td colspan="2">Mode</td>
                    <td colspan="2">2</td>
                </tr>
                <tr>
                    <td colspan="2">E-way Date</td>
                    <td colspan="2">2</td>
                </tr>
            </thead>
            <tbody>
                <tr rowspan="1">
                    <td rowspan="2" style="vertical-align: text-top;">S No</td>
                    <td rowspan="2" style="vertical-align: text-top;">Description</td>
                    <td rowspan="2" style="vertical-align: text-top;">HSN / SAC</td>
                    <td rowspan="2" style="vertical-align: text-top;">QTY</td>
                    <td rowspan="2" style="vertical-align: text-top;">Rate</td>
                    <td rowspan="2" style="vertical-align: text-top;">Discount</td>
                    <td colspan="2" style="vertical-align: text-top;">CGST</td>
                    <td colspan="2" style="vertical-align: text-top;">SGST</td>
                    <td rowspan="2" style="vertical-align: text-top;">Cess</td>
                    <td rowspan="2" style="vertical-align: text-top;">Amount</td>
                </tr>
                <tr>
                    <td>%</td>
                    <td>Amt</td>
                    <td>%</td>
                    <td>Amt</td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td colspan="6" style="vertical-align: text-top;">
                        <p>Total in Words:</p>
                        <p>Payment Mode:</p>
                    </td>
                    <td colspan="6" rowspan="3" style="vertical-align: bottom;">
                        <p style="width:80%;float: left;text-align: right;">Total Amount Before Tax</p>
                        <p style="width:20%;float: left;text-align: right;">200</p>
                        <p style="width:80%;float: left;text-align: right;">Add: CGST (12%)</p>
                        <p style="width:20%;float: left;text-align: right;">200</p>
                        <p style="width:80%;float: left;text-align: right;">SGST (12%)</p>
                        <p style="width:20%;float: left;text-align: right;">200</p>
                        <p style="width:80%;float: left;text-align: right;">CESS </p>
                        <p style="width:20%;float: left;text-align: right;">200</p>
                        <p style="width:80%;float: left;text-align: right;">Total </p>
                        <p style="width:20%;float: left;text-align: right;">200</p>
                        <p style="width:80%;float: left;text-align: right;">Round Off</p>
                        <p style="width:20%;float: left;text-align: right;">200</p>
                        <p style="width:80%;float: left;text-align: right;">Grand Total</p>
                        <p style="width:20%;float: left;text-align: right;">200</p>
                        <p style="width:80%;float: left;text-align: right;">Payment Made</p>
                        <p style="width:20%;float: left;text-align: right;">200</p>
                        <p style="width:80%;float: left;text-align: right;">Balance Due</p>
                        <p style="width:20%;float: left;text-align: right;">200</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2" style="vertical-align: text-top;">Bank Delail:</td>
                    <td colspan="3">Axis, Kathua</td>
                </tr>
                <tr>
                    <td colspan="3" style="vertical-align: text-top;">
                        <p>Ac No:</p>
                        <p>IFSC:</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="8">
                        <h4>Terms & Conditions:</h4>
                        <ul class="term">
                            <li>If the bill is not paid with in 30 days interest 24% will be charged from the date of
                                bill. </li>
                            <li>In the event of any dispute of whatever nature Kathua court only will have jurisdiction.
                            </li>
                            <li>Good once sold cannot be taken back.</li>
                        </ul>
                        <p class="buyer-sign" style="vertical-align: bottom;">Buyer Sign</p>
                    </td>
                    <td colspan="4" style="vertical-align: bottom;">
                        <p>Company Name</p>
                        <p class="mr-top">Authorised Signature</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Simple Invoice - END -->
    </div>
</body>

</html>