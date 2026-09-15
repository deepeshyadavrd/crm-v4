<style>
    .seen {
        background: #f1f1f1;
        opacity: 0.6;
    }

    .seen h4 {
        text-decoration: line-through;
    }

    th a {
        color: #f96332 !important;
    }
</style>

<section class="content ecommerce-page">

    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>
                    Current Leads
                    <small class="text-muted">
                        Welcome to UrbanWood Leads
                    </small>
                </h2>
            </div>
        </div>
    </div>

    <div class="container-fluid">

        <!-- ASSIGN / UNASSIGN -->
        <div class="row">

            <!-- ASSIGN -->
            <div class="col-6">

                <h6>Assign Leads</h6>
                <hr>

                <?php
                $userGroupId = session()->get('user_group_id');

                if (
                    $userGroupId == 1 ||
                    $userGroupId == 17 ||
                    $userGroupId == 21
                ) {
                ?>

                    <form
                        id="leadsubform"
                        action="<?= site_url('leads/assign') ?>"
                        method="post"
                    >

                        <div class="row clearfix">

                            <div class="col-3">
                                <label for="lead_id">
                                    Select a lead:
                                </label>
                            </div>

                            <div class="col-9">

                                <select
                                    id="leads"
                                    name="lead_id[]"
                                    multiple
                                    class="form-control"
                                >

                                    <?php foreach ($leads as $key => $lead) {

                                        if ($lead->assignedto == NULL) {
                                    ?>

                                        <option value="<?= $lead->request_callback_id ?>">
                                            <?= esc($lead->name) ?> -
                                            <?= esc($lead->state) ?>
                                        </option>

                                    <?php
                                        }
                                    }
                                    ?>

                                </select>

                            </div>

                            <div class="col-3">
                                <label for="salesperson_id">
                                    Assign to a salesperson:
                                </label>
                            </div>

                            <div class="col-9">

                                <select
                                    id="sps"
                                    name="salesperson_id"
                                    class="form-control"
                                >

                                    <?php foreach ($salespeople as $key => $salesperson) { ?>

                                        <option value="<?= $salesperson->user_id ?>">
                                            <?= esc($salesperson->firstname) ?>
                                            <?= esc($salesperson->lastname) ?>
                                        </option>

                                    <?php } ?>

                                </select>

                            </div>

                            <div class="col-9">

                                <button
                                    type="submit"
                                    class="btn btn-raised btn-info btn-round"
                                >
                                    Assign Lead
                                </button>

                            </div>

                        </div>

                    </form>

                <?php } ?>

            </div>


            <!-- UNASSIGN -->
            <div class="col-6">

                <h6>Un Assign Leads</h6>
                <hr>

                <?php
                if (
                    $userGroupId == 1 ||
                    $userGroupId == 17 ||
                    $userGroupId == 21
                ) {
                ?>

                    <form
                        id="leadusform"
                        action="<?= site_url('leads/unassign') ?>"
                        method="post"
                    >

                        <div class="row clearfix">

                            <div class="col-3">
                                <label for="lead_id">
                                    Select a lead:
                                </label>
                            </div>

                            <div class="col-9">

                                <select
                                    id="asleads"
                                    name="lead_id[]"
                                    multiple
                                    class="form-control"
                                >

                                    <?php foreach ($leads as $key => $lead) {

                                        if ($lead->assignedto != NULL) {
                                    ?>

                                        <option value="<?= $lead->request_callback_id ?>">
                                            <?= esc($lead->name) ?> -
                                            <?= esc($lead->state) ?> -
                                            <?= esc($lead->assignedto) ?>
                                        </option>

                                    <?php
                                        }
                                    }
                                    ?>

                                </select>

                            </div>

                            <div class="col-12">

                                <button
                                    type="submit"
                                    class="btn btn-raised btn-info btn-round"
                                >
                                    Un-Assign Lead
                                </button>

                            </div>

                        </div>

                    </form>

                <?php } ?>

            </div>

        </div>


        <!-- LEADS TABLE -->
        <div class="row clearfix">

            <div class="col-sm-12 col-md-12 col-lg-12">

                <div class="card">

                    <div class="header d-flex flex-wrap align-items-center justify-content-between gap-3">

                        <h2 class="mb-0">
                            <strong>Recent</strong> Leads
                        </h2>


                        <?php
                        if (
                            in_array(
                                $userGroupId,
                                [1, 17, 21]
                            )
                        ) {

                            $count = count(
                                array_filter($_GET)
                            );
                        ?>

                            <div class="d-flex gap-2">

                                <button
                                    class="btn btn-primary"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#filterCanvas"
                                >
                                    Filters

                                    <?php if ($count > 0) { ?>

                                        (<?= $count ?>)

                                    <?php } ?>

                                </button>


                                <?php if (!empty($_GET)) { ?>

                                    <a
                                        href="<?= site_url('leads') ?>"
                                        class="btn btn-outline-danger"
                                    >
                                        Clear Filters
                                    </a>

                                <?php } ?>

                            </div>

                        <?php } ?>


                        <?php
                        if (
                            in_array(
                                $userGroupId,
                                [1, 17, 21]
                            )
                        ) {
                        ?>

                            <div class="report-download d-flex align-items-center gap-2">

                                <div class="form-group">

                                    <label for="from">
                                        From
                                    </label>

                                    <input
                                        type="date"
                                        id="fromdate"
                                        class="form-control"
                                        required
                                    >

                                </div>


                                <div class="form-group">

                                    <label for="to">
                                        To
                                    </label>

                                    <input
                                        type="datetime-local"
                                        id="todate"
                                        class="form-control"
                                        required
                                    >

                                </div>


                                <input
                                    type="hidden"
                                    id="userid"
                                    value="<?= session()->get('user_id') ?>"
                                >


                                <button
                                    class="btn btn-raised btn-primary waves-effect btn-round update_product_status"
                                    onclick="getLeadsWithDate(
                                        $('#fromdate').val(),
                                        $('#todate').val(),
                                        $('#userid').val()
                                    )"
                                >
                                    Report
                                </button>

                            </div>

                        <?php } ?>

                    </div>


                    <div class="body table-responsive members_profiles">

                        <table
                            class="table table-hover js-exportable"
                            id="myTable"
                        >

                            <thead>

                                <tr>

                                    <th style="width:60px;">
                                        #ID
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th data-dynatable-sorts="Indate">
                                        Email/Mobile
                                    </th>

                                    <th>
                                        State
                                    </th>

                                    <th>
                                        Stage
                                    </th>

                                    <th>
                                        Assigned
                                    </th>

                                    <th>
                                        Indate
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Source
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="table-items">

                                <?php if (!empty($leads)) {

                                    foreach ($leads as $key => $lead) {
                                ?>

                                    <tr>

                                        <td>
                                            #<?= esc($lead->request_callback_id) ?>
                                        </td>


                                        <td>
                                            <?= esc($lead->name) ?>
                                        </td>


                                        <td>
                                            <?= esc($lead->email) ?>
                                            <br>
                                            <?= esc($lead->mobile) ?>
                                        </td>


                                        <td>
                                            <?= esc($lead->state) ?>
                                        </td>


                                        <!-- STAGE -->
                                        <td>

                                            <?php if ($lead->stage == 1) { ?>

                                                <span class="badge bg-amber">
                                                    Hot
                                                </span>

                                            <?php } elseif ($lead->stage == 0) { ?>

                                                <span class="badge bg-blue">
                                                    Cold
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge bg-red">
                                                    Closed
                                                </span>

                                            <?php } ?>

                                        </td>


                                        <!-- ASSIGNED -->
                                        <td>

                                            <?php
                                            if (isset($lead->assignedto)) {
                                                echo esc($lead->assignedto);
                                            } else {
                                                echo '--';
                                            }
                                            ?>

                                        </td>


                                        <!-- DATE -->
                                        <td>

                                            <?= date(
                                                'd/m/y H:i A',
                                                strtotime($lead->date_added)
                                            ) ?>

                                        </td>


                                        <!-- STATUS -->
                                        <td>

                                            <?php if ($lead->status == 1) { ?>

                                                <span class="badge bg-blue-grey">
                                                    PENDING
                                                </span>

                                            <?php } elseif ($lead->status == 2) { ?>

                                                <span class="badge bg-teal">
                                                    Assigned
                                                </span>

                                            <?php } elseif ($lead->status == 3) { ?>

                                                <span class="badge bg-blue">
                                                    Call Back
                                                </span>

                                            <?php } elseif ($lead->status == 4) { ?>

                                                <span class="badge bg-amber">
                                                    Follow Up
                                                </span>

                                            <?php } elseif ($lead->status == 5) { ?>

                                                <span class="badge bg-teal">
                                                    WhatsApp
                                                </span>

                                            <?php } elseif ($lead->status == 6) { ?>

                                                <span class="badge bg-red">
                                                    Closed
                                                </span>

                                            <?php } elseif ($lead->status == 8) { ?>

                                                <span class="badge bg-red">
                                                    Not Answering 1
                                                </span>

                                            <?php } elseif ($lead->status == 9) { ?>

                                                <span class="badge bg-red">
                                                    Not Answering 2
                                                </span>

                                            <?php } elseif ($lead->status == 10) { ?>

                                                <span class="badge bg-red">
                                                    Not Answering 3
                                                </span>

                                            <?php } elseif ($lead->status == 11) { ?>

                                                <span class="badge bg-red">
                                                    Wrong Number
                                                </span>

                                            <?php } elseif ($lead->status == 12) { ?>

                                                <span class="badge bg-red">
                                                    Not Interested
                                                </span>

                                            <?php } elseif ($lead->status == 13) { ?>

                                                <span class="badge bg-red">
                                                    Low Budget
                                                </span>

                                            <?php } elseif ($lead->status == 14) { ?>

                                                <span class="badge bg-red">
                                                    Can't Make
                                                </span>

                                            <?php } elseif ($lead->status == 15) { ?>

                                                <span class="badge bg-red">
                                                    Junk Lead
                                                </span>

                                            <?php } elseif ($lead->status == 16) { ?>

                                                <span class="badge bg-red">
                                                    Repeated Lead
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge bg-green">
                                                    Order Received
                                                </span>

                                            <?php } ?>

                                        </td>


                                        <!-- SOURCE -->
                                        <td>
                                            <?= esc($lead->lead_source) ?>
                                        </td>


                                        <!-- ACTION -->
                                        <td>

                                            <a
                                                class="btn"
                                                href="<?= site_url('leads/view/' . $lead->request_callback_id) ?>"
                                            >
                                                View
                                            </a>

                                        </td>

                                    </tr>

                                <?php
                                    }
                                }
                                ?>

                            </tbody>

                        </table>


                        <p>
                            <?= $links ?>
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- FILTER OFFCANVAS -->
<div
    class="offcanvas offcanvas-end"
    tabindex="-1"
    id="filterCanvas"
>

    <div class="offcanvas-header">

        <h5>
            Filter Leads
        </h5>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
        ></button>

    </div>


    <div class="offcanvas-body">

        <form method="GET" action="">

            <!-- FROM DATE -->
            <div class="mb-3">

                <label>
                    From Date
                </label>

                <input
                    type="date"
                    name="from_date"
                    class="form-control"
                    value="<?= esc($_GET['from_date'] ?? '') ?>"
                >

            </div>


            <!-- TO DATE -->
            <div class="mb-3">

                <label>
                    To Date
                </label>

                <input
                    type="date"
                    name="to_date"
                    class="form-control"
                    value="<?= esc($_GET['to_date'] ?? '') ?>"
                >

            </div>


            <!-- STATUS -->
            <div class="mb-3">

                <label>
                    Status
                </label>

                <select
                    name="status"
                    class="form-control"
                >

                    <option value="">
                        All
                    </option>


                    <option
                        value="1"
                        <?= (($_GET['status'] ?? '') == '1') ? 'selected' : '' ?>
                    >
                        Pending
                    </option>


                    <option
                        value="2"
                        <?= (($_GET['status'] ?? '') == '2') ? 'selected' : '' ?>
                    >
                        Assigned
                    </option>


                    <option
                        value="3"
                        <?= (($_GET['status'] ?? '') == '3') ? 'selected' : '' ?>
                    >
                        Call Back
                    </option>


                    <option
                        value="4"
                        <?= (($_GET['status'] ?? '') == '4') ? 'selected' : '' ?>
                    >
                        Follow Up
                    </option>


                    <option
                        value="6"
                        <?= (($_GET['status'] ?? '') == '6') ? 'selected' : '' ?>
                    >
                        Closed
                    </option>


                    <option
                        value="7"
                        <?= (($_GET['status'] ?? '') == '7') ? 'selected' : '' ?>
                    >
                        Order Received
                    </option>


                    <option
                        value="8"
                        <?= (($_GET['status'] ?? '') == '8') ? 'selected' : '' ?>
                    >
                        Not Answering 1
                    </option>


                    <option
                        value="9"
                        <?= (($_GET['status'] ?? '') == '9') ? 'selected' : '' ?>
                    >
                        Not Answering 2
                    </option>


                    <option
                        value="10"
                        <?= (($_GET['status'] ?? '') == '10') ? 'selected' : '' ?>
                    >
                        Not Answering 3
                    </option>


                    <option
                        value="11"
                        <?= (($_GET['status'] ?? '') == '11') ? 'selected' : '' ?>
                    >
                        Wrong Number
                    </option>


                    <option
                        value="12"
                        <?= (($_GET['status'] ?? '') == '12') ? 'selected' : '' ?>
                    >
                        Not Interested
                    </option>

                </select>

            </div>


            <!-- SALESPERSON -->
            <div class="mb-3">

                <label>
                    Sales Person
                </label>

                <select
                    name="salesperson"
                    class="form-control"
                >

                    <option value="">
                        All
                    </option>


                    <?php foreach ($salespeople as $key => $salesperson) { ?>

                        <option
                            value="<?= $salesperson->user_id ?>"
                            <?= (
                                ($_GET['salesperson'] ?? '')
                                == $salesperson->user_id
                            ) ? 'selected' : '' ?>
                        >

                            <?= esc($salesperson->firstname) ?>
                            <?= esc($salesperson->lastname) ?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- BUTTONS -->
            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Apply Filters
                </button>

            </div>

        </form>

    </div>

</div>


<!-- LEAD SEARCH -->
<script>

$(document).ready(function () {

    $('#input_value').keyup(function () {

        if (this.value.length > 3) {

            var input_value = $(this).val();

            $.ajax({

                type: 'post',

                url: '<?= site_url('leads/search') ?>',

                data: {
                    input_value: input_value
                },

                success: function (response) {

                    $('#searchmain').css(
                        'display',
                        'block'
                    );

                    var responsejson = response;

                    console.log(responsejson);

                    if (!$.trim(responsejson)) {

                        var html =
                            '<li>' +
                            '<div class="menu-info">' +
                            '<div class="icon-circle bg-red">' +
                            '<i class="material-icons">close</i>' +
                            '</div>' +
                            '<h4>Not Match</h4>' +
                            '</div>' +
                            '</li>';

                        $('#searchlist').html(html);

                    } else {

                        var html = '';

                        $.each(
                            responsejson,
                            function (key, value) {

                                var statustxt = '';

                                if (value.status == 1) {

                                    statustxt =
                                        '<span class="badge bg-blue-grey">PENDING</span>';

                                } else if (value.status == 2) {

                                    statustxt =
                                        '<span class="badge bg-teal">Assigned</span>';

                                } else if (value.status == 3) {

                                    statustxt =
                                        '<span class="badge bg-blue">Call Back</span>';

                                } else if (value.status == 4) {

                                    statustxt =
                                        '<span class="badge bg-amber">Follow Up</span>';

                                } else if (value.status == 5) {

                                    statustxt =
                                        '<span class="badge bg-teal">WhatsApp</span>';

                                } else if (value.status == 6) {

                                    statustxt =
                                        '<span class="badge bg-red">Closed</span>';

                                } else if (value.status == 7) {

                                    statustxt =
                                        '<span class="badge bg-green">Order Received</span>';

                                } else if (value.status == 8) {

                                    statustxt =
                                        '<span class="badge bg-red">Not Answering 1</span>';

                                } else if (value.status == 9) {

                                    statustxt =
                                        '<span class="badge bg-red">Not Answering 2</span>';

                                } else if (value.status == 10) {

                                    statustxt =
                                        '<span class="badge bg-red">Not Answering 3</span>';

                                } else if (value.status == 11) {

                                    statustxt =
                                        '<span class="badge bg-red">Wrong Number</span>';

                                } else if (value.status == 12) {

                                    statustxt =
                                        '<span class="badge bg-red">Not Interested</span>';

                                } else if (value.status == 13) {

                                    statustxt =
                                        '<span class="badge bg-red">Low Budget</span>';

                                } else if (value.status == 14) {

                                    statustxt =
                                        '<span class="badge bg-red">Can\'t make</span>';

                                } else if (value.status == 15) {

                                    statustxt =
                                        '<span class="badge bg-red">Junk Lead</span>';

                                } else {

                                    statustxt =
                                        '<span class="badge bg-red">Repeated Lead</span>';
                                }


                                html +=
                                    '<li>' +
                                    '<a href="<?= site_url('leads/view') ?>/' +
                                    value.request_callback_id +
                                    '">' +

                                    '<div class="icon-circle bg-blue">' +
                                    '<i class="zmdi zmdi-account"></i>' +
                                    '</div>' +

                                    '<div class="menu-info">' +

                                    '<h4>' +
                                    value.name +
                                    '</h4>' +

                                    '<p>' +
                                    '<i class="zmdi zmdi-time"></i> ' +
                                    statustxt +
                                    '</p>' +

                                    '</div>' +

                                    '</a>' +

                                    '</li>';

                            }
                        );

                        $('#searchlist').html(html);
                    }
                }

            });

        }

    });

});

</script>


<!-- DATE -->
<script>

$(function () {

    var dtToday = new Date();

    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();

    if (month < 10) {
        month = '0' + month.toString();
    }

    if (day < 10) {
        day = '0' + day.toString();
    }

    var maxDate =
        year + '-' + month + '-' + day;

    $('#txtDate').attr(
        'max',
        maxDate
    );

});


/*
 * Filter function
 */
function filterData(filterValue) {

    const items = $("#table-items tr");

    items.hide();

    items.filter(function () {

        return $(this)
            .text()
            .toLowerCase()
            .includes(
                filterValue.toLowerCase()
            );

    }).show();

}


/*
 * Generate leads report
 */
function getLeadsWithDate(from, to, user) {

    $.ajax({

        url: '<?= site_url('leads/getwithdate') ?>',

        type: 'post',

        data: {
            fromdate: $("#fromdate").val(),
            todate: $("#todate").val(),
            userid: user
        },

        success: function (data) {

            if (data == '') {
                return;
            }

            JSONToCSVConvertor(
                data,
                "Leads Report",
                true
            );

        }

    });

}


/*
 * JSON TO CSV
 */
function JSONToCSVConvertor(
    JSONData,
    ReportTitle,
    ShowLabel
) {

    var arrData =
        typeof JSONData != 'object'
            ? JSON.parse(JSONData)
            : JSONData;

    var CSV = '';

    CSV += ReportTitle + '\r\n\n';


    if (ShowLabel) {

        var row = '';

        for (var index in arrData[0]) {

            row += index + ',';

        }

        row = row.slice(0, -1);

        CSV += row + '\r\n';

    }


    for (
        var i = 0;
        i < arrData.length;
        i++
    ) {

        var row = '';

        for (
            var index in arrData[i]
        ) {

            if (
                typeof arrData[i][index]
                === 'object'
            ) {

                var data =
                    arrData[i][index];

                for (
                    var key in data
                ) {

                    for (
                        v in data[key]
                    ) {

                        someText =
                            data[key][v]
                                .replace(/(,)/gm, '');

                        someText =
                            $.trim(someText);

                        someText =
                            someText.replace(
                                /(\r\n|\n|\r)/gm,
                                ','
                            );

                        row +=
                            '"' +
                            someText +
                            '" , ,';

                    }

                    row =
                        row.slice(
                            0,
                            row.length - 1
                        );
                }

            } else {

                row +=
                    '"' +
                    arrData[i][index] +
                    '",';

            }

        }

        row.slice(
            0,
            row.length - 1
        );

        CSV += row + '\r\n';

    }


    if (CSV == '') {

        alert("Invalid data");

        return;

    }


    var fileName = "MyReport_";

    fileName +=
        ReportTitle.replace(
            / /g,
            "_"
        );


    var uri =
        'data:text/csv;charset=utf-8,' +
        escape(CSV);

    var link =
        document.createElement("a");

    link.href = uri;

    link.style =
        "visibility:hidden";

    link.download =
        fileName + ".csv";

    document.body.appendChild(link);

    link.click();

    document.body.removeChild(link);

}

</script>


<!-- DYNATABLE -->
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/Dynatable/0.3.1/jquery.dynatable.min.css"
>

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/Dynatable/0.3.1/jquery.dynatable.min.js"
></script>

<script>

$(document).ready(function () {

    $('#myTable').dynatable({

        features: {
            paginate: false,
            search: false,
            recordCount: false,
            perPageSelect: false
        }

    });

});

</script>


<!-- ASSIGN / UNASSIGN -->
<script>

$("#leadsubform").submit(function (e) {

    e.preventDefault();

    var leads = $('#leads').val();
    var sps = $('#sps').val();


    if (leads == '') {

        swal(
            "Error!",
            "Please select any option!",
            "error"
        );

        return false;
    }


    swal({

        title: "Are you sure?",

        text:
            "Once updated, you will not be able to recover this!",

        icon: "warning",

        buttons: true,

        dangerMode: true

    }).then(function (willDelete) {

        if (willDelete) {

            $.ajax({

                url:
                    '<?= site_url('leads/assign') ?>',

                type: 'POST',

                data: {
                    lead_id: leads,
                    salesperson_id: sps
                },

                success: function () {

                    swal(
                        "Done! Lead Assigned",
                        {
                            icon: "success"
                        }
                    ).then(function () {

                        location.reload();

                    });

                },

                error: function (err) {

                    swal(
                        "Error!",
                        err,
                        "error"
                    );

                }

            });

        }

    });

});


$("#leadusform").submit(function (e) {

    e.preventDefault();

    var asleads =
        $('#asleads').val();


    if (asleads == '') {

        swal(
            "Error!",
            "Please select any option!",
            "error"
        );

        return false;
    }


    swal({

        title: "Are you sure?",

        text:
            "Once updated, you will not be able to recover this!",

        icon: "warning",

        buttons: true,

        dangerMode: true

    }).then(function (willDelete) {

        if (willDelete) {

            $.ajax({

                url:
                    '<?= site_url('leads/unassign') ?>',

                type: 'POST',

                data: {
                    lead_id: asleads
                },

                success: function () {

                    swal(
                        "Lead Unassigned!",
                        {
                            icon: "warning"
                        }
                    ).then(function () {

                        location.reload();

                    });

                },

                error: function (err) {

                    swal(
                        "Error!",
                        err,
                        "error"
                    );

                }

            });

        }

    });

});


/*
 * Load reminders
 */
$(document).ready(function () {

    <?php if (!empty($getreminder)) { ?>

        $.ajax({

            type: 'GET',

            url:
                '<?= site_url('leads/getmyreminder') ?>',

            context: document.body,

            success: function (data) {

                if (JSON.parse(data) == 'empty') {

                    var html =
                        '<li>' +
                        '<div class="menu-info">' +
                        '<h4>' +
                        JSON.parse(data) +
                        '</h4>' +
                        '</div>' +
                        '</li>';

                    $('#reminders').html(html);

                } else {

                    var html = '';

                    data =
                        JSON.parse(data);

                    $.each(
                        data,
                        function (
                            i,
                            currProgram
                        ) {

                            let seenClass =
                                currProgram['is_seen'] == 1
                                    ? 'seen'
                                    : '';

                            html +=
                                '<li id="rmid_' +
                                currProgram['lr_id'] +
                                '" class="rmdr ' +
                                seenClass +
                                '" data-rmdrid="' +
                                currProgram['lr_id'] +
                                '">' +

                                '<a href="<?= site_url('leads/view') ?>/' +
                                currProgram['lead_id'] +
                                '" class="reminder-link" data-id="' +
                                currProgram['lr_id'] +
                                '">' +

                                '<div class="icon-circle bg-blue">' +
                                '<i class="zmdi zmdi-account"></i>' +
                                '</div>' +

                                '<div class="menu-info">' +

                                '<h4>' +
                                currProgram['title'] +
                                '</h4>' +

                                '<p>' +
                                '<i class="zmdi zmdi-time"></i> ' +
                                currProgram['reminder_date'] +
                                '</p>' +

                                '</div>' +

                                '</a>' +

                                '</li>';

                        }
                    );

                    $('#reminders').html(html);

                }

            }

        });

    <?php } ?>

});


/*
 * Mark reminder as seen
 */
$(document).on(
    'click',
    '.reminder-link',
    function () {

        let id =
            $(this).data('id');

        navigator.sendBeacon(
            '<?= site_url('leads/mark_seen') ?>/' + id
        );

        $(this)
            .closest('li')
            .addClass('seen');

    }
);

</script>