<link rel="stylesheet" href="<?= base_url('assets/css/timeline.css') ?>">

<!-- Light Gallery Plugin Css -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/light-gallery/css/lightgallery.css') ?>">


<section class="content">

    <div class="block-header">

        <div class="row">

            <div class="col-lg-7 col-md-6 col-sm-12">

                <h2>
                    #LEAD_ID : <?= esc($row['lead_id']) ?>

                    <small class="text-muted">
                        LEAD DETAIL PAGE
                    </small>
                </h2>

            </div>


            <div
                class="col-lg-5 col-md-6 col-sm-12"
                style="text-align:right;"
            >

                <p>
                    Assigned To:
                    <b><?= esc($row['assignedto']) ?></b>
                </p>

                <!--
                <button
                    class="btn btn-primary btn-round waves-effect"
                    type="button"
                    onclick="makecall(
                        <?= $sales_person_mobile ?>,
                        '<?= esc($sales_person_name) ?>',
                        <?= $row['mobile'] ?>
                    )"
                >
                    <i class="material-icons">call</i>
                </button>
                -->

            </div>

        </div>

    </div>


    <div class="container-fluid">

        <!-- LEAD DETAIL -->
        <div class="row clearfix">

            <div class="col-lg-12 col-md-12 col-sm-12">

                <div class="card">

                    <div class="body">

                        <div
                            class="head"
                            style="display:flex;justify-content:space-between;"
                        >

                            <h2 class="card-inside-title">
                                LEAD DETAIL
                            </h2>

                            <p>
                                Date:
                                <?= esc($row['date']) ?>
                            </p>

                        </div>


                        <div class="row clearfix">

                            <!-- LEFT -->
                            <div class="col-sm-6">

                                <table class="table table-striped m-b-0">

                                    <tbody>

                                        <tr>

                                            <td>
                                                LEAD ID
                                            </td>

                                            <td class="font-medium">
                                                #<?= esc($row['lead_id']) ?>
                                            </td>

                                        </tr>


                                        <tr>

                                            <td>
                                                Customer
                                            </td>

                                            <td class="font-medium">
                                                <?= esc($row['name']) ?>
                                            </td>

                                        </tr>


                                        <tr>

                                            <td>
                                                Email
                                            </td>

                                            <td class="font-medium">
                                                <?= esc($row['email']) ?>
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>


                            <!-- RIGHT -->
                            <div class="col-sm-6">

                                <table class="table table-striped m-b-0">

                                    <tbody>

                                        <tr>

                                            <td>
                                                Phone
                                            </td>

                                            <td class="font-medium">
                                                <?= esc($row['mobile']) ?>
                                            </td>

                                        </tr>


                                        <tr>

                                            <td>
                                                State
                                            </td>

                                            <td class="font-medium">
                                                <?= esc($row['state']) ?>
                                            </td>

                                        </tr>


                                        <tr>

                                            <td>
                                                Message
                                            </td>

                                            <td class="font-medium">
                                                <?= esc($row['message']) ?>
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>


                            <!-- IMAGE / PDF -->
                            <?php if (isset($row['image']) && !empty($row['image'])): ?>

                                <div class="col-lg-12 col-md-12 col-sm-12">

                                    <div class="card">

                                        <div class="header">

                                            <h2>
                                                <strong>Images</strong>
                                            </h2>

                                        </div>


                                        <div class="body">

                                            <div
                                                id="aniimated-thumbnials"
                                                class="list-unstyled row clearfix"
                                            >

                                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 m-b-20">

                                                    <?php
                                                    $fileExt = strtolower(
                                                        pathinfo(
                                                            $row['image'],
                                                            PATHINFO_EXTENSION
                                                        )
                                                    );

                                                    $imageUrl =
                                                        'https://www.urbanwood.in/' .
                                                        ltrim($row['image'], '/');
                                                    ?>


                                                    <?php if ($fileExt === 'pdf'): ?>

                                                        <iframe
                                                            src="<?= esc($imageUrl) ?>"
                                                            width="100%"
                                                            height="500px"
                                                            style="border:none;"
                                                        ></iframe>


                                                    <?php else: ?>

                                                        <a
                                                            href="<?= esc($imageUrl) ?>"
                                                        >

                                                            <img
                                                                class="img-fluid img-thumbnail"
                                                                src="<?= esc($imageUrl) ?>"
                                                                alt=""
                                                            >

                                                        </a>

                                                    <?php endif; ?>


                                                    <!-- DOWNLOAD -->
                                                    <div style="margin-top:10px;">

                                                        <a
                                                            href="<?= esc($imageUrl) ?>"
                                                            class="btn btn-primary"
                                                            target="_blank"
                                                            download
                                                        >
                                                            📥 Download
                                                            <?= strtoupper(esc($fileExt)) ?>
                                                        </a>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- UPDATE STATUS / REMINDER -->
        <div class="row">

            <!-- UPDATE STATUS -->
            <div
                class="col-7 block-header"
                style="display:flex;flex-direction:column;align-items:baseline;"
            >

                <h2>
                    Update Lead Status
                </h2>


                <label for="lead_status">
                    Lead Status
                </label>


                <select
                    name="lead_status"
                    id="lead_new_status<?= esc($row['lead_id']) ?>"
                    class="form-select"
                >

                    <?php foreach ($lead_status as $key => $leadstatus): ?>

                        <option
                            value="<?= esc($leadstatus->lead_status_id) ?>"
                            <?= (
                                $leadstatus->lead_status_id ==
                                $row['status']
                            ) ? 'selected' : '' ?>
                        >
                            <?= esc($leadstatus->status_name) ?>
                        </option>

                    <?php endforeach; ?>

                </select>


                <label for="lead_stage">
                    Lead Stage
                </label>


                <select
                    name="lead_stage"
                    id="lead_new_stage<?= esc($row['lead_id']) ?>"
                    class="form-select"
                >

                    <?php if ($row['stage'] == 1): ?>

                        <option value="1" selected>
                            HOT
                        </option>

                        <option value="0">
                            COLD
                        </option>

                        <option value="2">
                            CLOSED
                        </option>


                    <?php elseif ($row['stage'] == 0): ?>

                        <option value="1">
                            HOT
                        </option>

                        <option value="0" selected>
                            COLD
                        </option>

                        <option value="2">
                            CLOSED
                        </option>


                    <?php else: ?>

                        <option value="1">
                            HOT
                        </option>

                        <option value="0">
                            COLD
                        </option>

                        <option value="2" selected>
                            CLOSED
                        </option>

                    <?php endif; ?>

                </select>


                <button
                    class="btn btn-raised btn-primary waves-effect btn-round update_product_stage"
                    type="button"
                    onclick="update_stage(
                        '<?= esc($row['lead_id']) ?>',
                        $('#lead_new_status<?= esc($row['lead_id']) ?>').val(),
                        $('#lead_new_stage<?= esc($row['lead_id']) ?>').val()
                    )"
                >
                    Update
                </button>

            </div>


            <!-- REMINDER -->
            <div class="col-5">

                <h6>
                    Add Reminder
                </h6>


                <button
                    type="button"
                    class="btn btn-round btn-info waves-effect"
                    data-bs-toggle="modal"
                    data-bs-target="#addevent"
                >
                    Add Events
                </button>


                <?php if (!empty($reminders)): ?>

                    <table class="table table-striped m-b-0">

                        <thead>

                            <tr>

                                <td>
                                    Title
                                </td>

                                <td>
                                    Date
                                </td>

                                <td>
                                    Description
                                </td>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($reminders as $key => $reminder): ?>

                                <tr>

                                    <td class="font-medium">
                                        <?= esc($reminder->title) ?>
                                    </td>

                                    <td class="font-medium">
                                        <?= esc($reminder->reminder_date) ?>
                                    </td>

                                    <td class="font-medium">
                                        <?= esc($reminder->description) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

            </div>

        </div>


        <!-- REMARKS -->
        <div class="block-header">

            <div class="row">

                <div class="col-lg-12 col-md-12 col-sm-12">

                    <h2>
                        Add Remark
                    </h2>


                    <form id="remarksInput">

                        <input
                            type="hidden"
                            name="lead_id"
                            value="<?= esc($row['lead_id']) ?>"
                        >


                        <div class="form-group">

                            <textarea
                                class="form-control"
                                name="remark"
                                rows="3"
                            ></textarea>

                        </div>


                        <button
                            class="btn btn-primary btn-round"
                            type="submit"
                        >
                            Submit
                        </button>

                    </form>

                </div>


                <?php if (!empty($row['remarks'])): ?>

                    <table class="table table-striped m-b-0">

                        <thead>

                            <tr>

                                <td>
                                    id
                                </td>

                                <td>
                                    Comment
                                </td>

                                <td>
                                    Date
                                </td>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($row['remarks'] as $key => $remark): ?>

                                <tr>

                                    <td class="font-medium">
                                        <?= esc($remark->lead_id) ?>
                                    </td>

                                    <td class="font-medium">
                                        <?= esc($remark->remark) ?>
                                    </td>

                                    <td class="font-medium">
                                        <?= esc($remark->date_added) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

            </div>

        </div>


        <!-- PRODUCTS -->
        <?php if (!empty($row['products'])): ?>

            <?php foreach ($row['products'] as $key => $product): ?>

                <?php
                $pname = explode('(', $product->name);

                $productName = $pname[0] ?? '';
                $productType = isset($pname[1])
                    ? str_replace(')', '', $pname[1])
                    : '';
                ?>


                <div class="row clearfix">

                    <!-- PRODUCT -->
                    <div class="col-lg-6 col-md-12">

                        <div class="card weather2">

                            <img
                                src="https://www.urbanwood.in/image/<?= esc($product->image) ?>"
                                class="img-fluid"
                                alt="<?= esc($productName) ?>"
                            >


                            <table class="table table-striped m-b-0">

                                <tbody>

                                    <tr>

                                        <td>
                                            Product
                                        </td>

                                        <td class="font-medium">
                                            <?= esc($productName) ?>
                                        </td>

                                    </tr>


                                    <tr>

                                        <td>
                                            Finish / Fabric / Type
                                        </td>

                                        <td class="font-medium">
                                            <?= esc($productType) ?>
                                        </td>

                                    </tr>


                                    <tr>

                                        <td>
                                            Quantity
                                        </td>

                                        <td class="font-medium">
                                            <?= esc($product->quantity) ?>
                                        </td>

                                    </tr>


                                    <tr>

                                        <td>
                                            Total
                                        </td>

                                        <td class="font-medium">
                                            <?= esc($product->total) ?>
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>


                    <!-- PRODUCT HISTORY -->
                    <div class="col-lg-6 col-md-6 col-sm-6">

                        <ul class="cbp_tmtimeline">

                            <?php if (!empty($product->history)): ?>

                                <?php foreach ($product->history as $key => $history): ?>

                                    <?php

                                    $order_status_text = '';
                                    $icon = '';

                                    if ($history->status == 1) {
                                        $order_status_text = 'Order In Process';
                                        $icon = 'zmdi zmdi-check';
                                    }

                                    if ($history->status == 2) {
                                        $order_status_text = 'Under Manufacturing';
                                        $icon = 'zmdi zmdi-wrench';
                                    }

                                    if ($history->status == 3) {
                                        $order_status_text = 'Product Packed';
                                        $icon = 'zmdi zmdi-truck';
                                    }

                                    if ($history->status == 4) {
                                        $order_status_text = 'Product Dispacthed';
                                    }

                                    if ($history->status == 9) {
                                        $order_status_text = 'In Transit';
                                    }

                                    if ($history->status == 10) {
                                        $order_status_text = 'Out For Delivery';
                                    }

                                    if ($history->status == 5) {
                                        $order_status_text = 'Product Delivered';
                                    }

                                    if ($history->status == 6) {
                                        $order_status_text = 'Return Requested';
                                    }

                                    if ($history->status == 7) {
                                        $order_status_text = 'Product Returned';
                                    }

                                    if ($history->status == 8) {
                                        $order_status_text = 'Item Cancelled';
                                    }

                                    ?>


                                    <li>

                                        <time
                                            class="cbp_tmtime"
                                            datetime="<?= esc($history->indate) ?>"
                                        >

                                            <small class="hidden">
                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime($history->indate)
                                                ) ?>
                                            </small>

                                        </time>


                                        <div class="cbp_tmicon">

                                            <i class="<?= esc($icon) ?>"></i>

                                        </div>


                                        <div class="cbp_tmlabel empty">

                                            <span>
                                                <?= esc($order_status_text) ?>
                                            </span>

                                        </div>

                                    </li>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </ul>


                        <!-- ADD PRODUCT HISTORY -->
                        <div class="card">

                            <div class="header">

                                <h2>
                                    <strong>
                                        Add Product History
                                    </strong>
                                </h2>


                                <ul class="header-dropdown">

                                    <li class="remove">

                                        <a
                                            role="button"
                                            class="boxs-close"
                                        >
                                            <i class="zmdi zmdi-close"></i>
                                        </a>

                                    </li>

                                </ul>

                            </div>


                            <div class="body">

                                <div class="row clearfix">

                                    <div class="col-sm-6">

                                        <select
                                            id="pro_new_status<?= esc($product->order_product_id) ?>"
                                            class="<?= esc($product->order_product_id) ?> form-select"
                                        >

                                            <option value="">
                                                -- Please select --
                                            </option>

                                            <option value="1">
                                                Order Processing
                                            </option>

                                            <option value="2">
                                                Under Manufacturing
                                            </option>

                                            <option value="3">
                                                Product Packed
                                            </option>

                                            <option value="4">
                                                Product Dispatched
                                            </option>

                                            <option value="9">
                                                In Transit
                                            </option>

                                            <option value="10">
                                                Out For Delivery
                                            </option>

                                            <option value="5">
                                                Delivered
                                            </option>

                                            <option value="6">
                                                Return Requested
                                            </option>

                                            <option value="7">
                                                Product Returned
                                            </option>

                                            <option value="8">
                                                Cancelled
                                            </option>

                                        </select>

                                    </div>


                                    <div class="col-sm-6">

                                        <button
                                            class="btn btn-raised btn-primary waves-effect btn-round update_product_status"
                                            type="button"
                                            onclick="update_status(
                                                '<?= esc($product->order_product_id) ?>',
                                                $('#pro_new_status<?= esc($product->order_product_id) ?>').val()
                                            )"
                                        >
                                            Update
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</section>


<!-- ADD REMINDER MODAL -->
<div
    class="modal fade"
    id="addevent"
    tabindex="-1"
    aria-labelledby="defaultModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h4
                    class="modal-title"
                    id="defaultModalLabel"
                >
                    Add Event
                </h4>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <div class="modal-body">

                <form id="reminderForm">

                    <?php
                    date_default_timezone_set('Asia/Kolkata');

                    $date1 =
                        date('Y-m-d\TH:i');

                    $date =
                        strtotime("+10 day");

                    $date2 =
                        date('Y-m-d\TH:i', $date);
                    ?>


                    <!-- DATE -->
                    <div class="form-group mb-3">

                        <input
                            type="datetime-local"
                            class="form-control"
                            placeholder="Event Date"
                            id="txtDate"
                            min="<?= $date1 ?>"
                            max="<?= $date2 ?>"
                        >

                    </div>


                    <!-- TITLE -->
                    <div class="form-group mb-3">

                        <input
                            type="text"
                            class="form-control"
                            placeholder="Event Title"
                            id="title"
                        >

                    </div>


                    <!-- HIDDEN VALUES -->
                    <input
                        type="hidden"
                        id="r_lead_id"
                        value="<?= esc($row['lead_id']) ?>"
                    >


                    <input
                        type="hidden"
                        id="sales_person_id"
                        value="<?= esc($sales_person_id) ?>"
                    >


                    <!-- DESCRIPTION -->
                    <div class="form-group mb-3">

                        <textarea
                            class="form-control no-resize"
                            id="descript"
                            placeholder="Event Description..."
                            spellcheck="false"
                        ></textarea>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary btn-round waves-effect"
                    >
                        Add
                    </button>


                    <button
                        type="button"
                        class="btn btn-simple btn-round waves-effect"
                        data-bs-dismiss="modal"
                    >
                        CLOSE
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<!-- STATUS / REMINDER JS -->
<script>

function update_status(lid, lstatus) {

    if (lstatus == '') {

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
                    '<?= site_url('leads/update_lead_status') ?>',

                type: 'POST',

                data: {

                    lid: lid,

                    opstatus: lstatus

                },

                success: function () {

                    swal(
                        "Done! Product Status has been updated!",
                        {
                            icon: "success"
                        }
                    );

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

}


/*
 * LEAD STAGE UPDATE
 */
function update_stage(lid, lstatus, lstage) {

    if (lstage == '') {

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
                    '<?= site_url('leads/update_lead_stage') ?>',

                type: 'POST',

                data: {

                    lid: lid,

                    lpstage: lstage,

                    lpstatus: lstatus

                },

                success: function () {

                    location.reload();

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

}


/*
 * ADD REMINDER
 */
$("#reminderForm").submit(function (e) {

    e.preventDefault();

    var txtDate =
        $('#txtDate').val();

    var title =
        $('#title').val();

    var des =
        $('#descript').val();

    var lead_id =
        $('#r_lead_id').val();

    var sales_person_id =
        $('#sales_person_id').val();


    if (txtDate == '') {

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
                    '<?= site_url('leads/addReminder') ?>',

                type: 'POST',

                data: {

                    txtDate: txtDate,

                    title: title,

                    descript: des,

                    lead_id: lead_id,

                    sales_person_id: sales_person_id

                },

                success: function (data) {

                    swal(
                        "Done! Product Stage has been updated!",
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

</script>


<!-- REMARKS / CALL -->
<script>

$('form#remarksInput').submit(function (e) {

    var form =
        $(this);

    e.preventDefault();


    $.ajax({

        type: "POST",

        url:
            "<?= site_url('leads/addRemark') ?>",

        data:
            form.serialize(),

        dataType:
            "html",

        success: function (data) {

            location.reload();

        },

        error: function () {

            alert(
                "Error posting feed."
            );

        }

    });

});


function makecall(
    ex_number,
    ex_name,
    client_number
) {

    $.ajax({

        url:
            'https://ivr.ivrguru.com:9094/api/v1/c2c?key=20bb78ba-ddc4-4462-b52b-fac1e8b44778&clientid=e290b34d-4813-495c-8e4c-1f75c8b5e277&executiveContact=' +
            ex_number +
            '&clientContact=' +
            client_number +
            '&executiveName=' +
            ex_name,

        type: 'get',

        dataType: 'jsonp',

        success: function (data) {

            console.log(data);

        }

    });

}

</script>