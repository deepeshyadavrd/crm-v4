<style>
  .product-item {
    cursor: pointer;
    transition: 0.2s;
}
.product-item:hover {
    background: #f8f9fa;
}

#search_view, #detail_view {
    transition: all 0.2s ease;
}
  </style>
<div class="modal fade" id="productSearchModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Search Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="search_view">
          <!-- Search Box -->
          <input type="text" id="product_search" class="form-control mb-3" placeholder="Type product name or SKU">
          <!-- Results -->
          <div id="search_results"></div>
        </div>

        <!-- DETAIL VIEW -->
        <div id="detail_view" style="display:none;">
          <button class="btn btn-light mb-3" id="back_to_search">
            ← Back
          </button>
          <div id="product_detail"></div>
        </div>
      </div>
      <!-- <div id="selected_product" class="mt-3"></div> -->
    </div>
  </div>
</div>
<!-- Jquery Core Js --> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?=base_url('assets');?>/bundles/libscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js --> 
<script src="<?=base_url('assets');?>/bundles/vendorscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js --> 

<script src="<?=base_url('assets');?>/bundles/countTo.bundle.js"></script>
<script src="<?=base_url('assets');?>/bundles/sparkline.bundle.js"></script>
<script src="<?=base_url('assets');?>/bundles/knob.bundle.js"></script>
<script src="<?=base_url('assets');?>/bundles/morrisscripts.bundle.js"></script><!-- Morris Plugin Js -->

<script src="<?=base_url('assets');?>/bundles/mainscripts.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Dynatable/0.3.1/jquery.dynatable.min.js"></script>
<script src="<?=base_url('assets');?>/js/pages/charts/jquery-knob.js"></script>
<script src="<?=base_url('assets');?>/js/pages/index2.js"></script>
<script src="<?=base_url('assets');?>/plugins/multi-select/js/jquery.multi-select.js"></script> <!-- Multi Select Plugin Js --> 
<!-- <script src="<?=base_url('assets');?>/js/pages/forms/advanced-form-elements.js"></script>  -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="<?=base_url('assets');?>/plugins/light-gallery/js/lightgallery-all.min.js"></script> <!-- Light Gallery Plugin Js --> 
<script src="<?=base_url('assets');?>/js/pages/medias/image-gallery.js"></script>
<script>
    /*global $ */
    $(document).ready(function() {
      "use strict";
      $('.menu > ul > li:has( > ul)').addClass('menu-dropdown-icon');
      $('.menu > ul > li > ul:not(:has(ul))').addClass('normal-sub');
      $(".menu > ul > li").hover(function(e) {
        if ($(window).width() > 943) {
          $(this).children("ul").stop(true, false).fadeToggle(150);
          e.preventDefault();
        }
      });  
      $(".menu > ul > li").click(function() {
        if ($(window).width() <= 943) {
          $(this).children("ul").fadeToggle(150);
        }
      });
    
      $(".h-bars").click(function(e) {
        $(".menu > ul").toggleClass('show-on-mobile');
        e.preventDefault();
      });
    
    });    

  
$('.rmdr').click(function(){

  alert($(this).data('rmdrid'));
});

$(document).mouseup(function(e) {
    var container = $("#searchmain");

    if (!container.is(e.target) && container.has(e.target).length === 0) 
    {
        container.hide();
    }
});
//PRODUCT Fetch

$('#productSearchModal').on('shown.bs.modal', function () {
    $('#product_search').focus();
});

let timer;

$('#product_search').on('keyup', function() {
    let query = $(this).val();

    clearTimeout(timer);

    timer = setTimeout(function() {
        if (query.length < 2) {
            $('#search_results').html('');
            return;
        }

        $.ajax({
            url: "<?= base_url('leads/searchProduct') ?>",
            method: "GET",
            data: { term: query },
            success: function(data) {
              // console.log(data);
                let products = JSON.parse(data);
                let html = '<div class="row">';

                if (products.length > 0) {
                    products.forEach(function(item) {
                      let price = Math.trunc(parseFloat(item.original_price.replace(/,/g, '')));
        let special = item.special_price ? Math.trunc(parseFloat(item.special_price.replace(/,/g, ''))) : null;
        // console.log(item.special_price);
        // console.log(special);
        // 2. Format with Indian commas (e.g., 73,999)
        let formattedPrice = '₹' + price.toLocaleString('en-IN');
        let formattedSpecial = special ? '₹' + special.toLocaleString('en-IN') : null;

        // console.log(formattedSpecial);
                        html += `<div class="col-6 col-md-6 col-lg-3 mb-3">
                        <div class="border rounded p-2 h-100 product-item text-center" data-id="${item.id}" data-oprice="${item.price}" data-sprice="${special}" data-name="${item.name}" data-image="${item.image}">
                            <img src="${item.image}" width="60" height="60" class="me-3 rounded">
                            <div>
                                <div class="fw-bold">${item.name}</div>
                                <div class="text-success">₹${special}</div>
                                <div class="text-danger">₹${price}</div>
                            </div>
                        </div></div>`;
                    });
                } else {
                    html = '<div class="text-muted">No products found</div>';
                }
                html += '</div>';
                $('#search_results').html(html);
            }
        });

    }, 300); // debounce
});

$(document).on('click', '.product-item', function() {

let id = $(this).data('id');
let name = $(this).data('name');
let oprice = $(this).data('oprice');
let sprice = $(this).data('sprice');
let image = $(this).data('image');

let html = `
    <div class="text-center">

        <img src="${image}" class="img-fluid rounded mb-3" style="max-height:530px;">

        <h5 class="fw-bold">${name}</h5>

        <div class="text-success fs-4 mb-2">₹${sprice}</div>

        <div class="text-muted">Product ID: ${id}</div>

    </div>
`;

$('#product_detail').html(html);

// 🔁 Switch views
$('#search_view').hide();
$('#detail_view').show();

});

$('#back_to_search').on('click', function() {
    $('#detail_view').hide();
    $('#search_view').show();
});

setInterval(function(){
  $.get('/notifications/unread_notifications', function(res){
    let data = JSON.parse(res);

    // 🔢 UPDATE COUNT
    let count = data.length;

    if(count > 0){
        $('#leadCount').text(count).show();
    } else {
        $('#leadCount').hide();
    }

    if(data.length > 0){
       showPopup(data[0].message);
         // sound
       var audio = new Audio('assets/notification.mp3');
       audio.play();
       if(Notification.permission === "granted"){
         new Notification("New CRM Lead 🚀", {
           body: "You have " + count + " unassigned leads"
         });
       }
    }
  });
}, 30000);

function showPopup(msg){
  // alert(msg); // later replace with toast
}

// // sound
// var audio = new Audio('assets/notification.mp3');
// audio.play();
$(document).ready(function(){

Notification.requestPermission();

});
    </script>    
    
</body>
</html>