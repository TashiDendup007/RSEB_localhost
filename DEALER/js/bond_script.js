function get_symbols_list(se_type) {
  if (se_type == '') {
    $("#symbol_div_id").html('');
    $("#bond_details_id").html('');
    return false;
  }

  const operation = "get_symbols_list";
  $.ajax({
    type: "POST",
    url: "bond_load_function.php",
    data: { get_symbols_list: operation, sec_type: se_type},
    dataType: "html",
    success: function(response) {
      $("#symbol_div_id").show().html(response);
    }
  });
}

function get_bond_dtls(sym_id) {
  if (sym_id == '') {
    $("#bond_details_id").html('');
    return false;
  }

  $.ajax({
    type: "POST",
    url: "bond_load_function.php",
    data: { get_bond_details: 'get_bond_details', symbol_id: sym_id},
    dataType: "html",
    success: function(response) {
      $("#bond_details_id").show().html(response);
    }
  });
}

function get_vol_fun(side) {
  const $holdingDiv = $("#holding_vol_div");
  // const $avlDiv = $("#avl_amt_div");
  const $priceDivId = $("#price_div_id");
  const $accDivId = $("#acc_div_id");
  const $dirtyDivId = $("#dirty_div_id");
  const $ytmDivId = $("#ytm_div_id");

  // Show case (Buy or empty)
  if (side === 'B' || side === '') {
    $holdingDiv.show().empty();
    $priceDivId.hide();
    $accDivId.hide();
    $dirtyDivId.hide();
    $ytmDivId.hide();
    // $avlDiv.show();
    return;
  }
  // display if side = S
  $priceDivId.show();
  $accDivId.show();
  $dirtyDivId.show();
  $ytmDivId.show();
  // Hide available amount for Sell
  // $avlDiv.hide();

  const cd_code = $("#cd_code").val();
  const symbol_id = $("#symbol_id").val();

  // Only call AJAX if BOTH values exist
  if (cd_code && symbol_id) {
    $.ajax({
      type: "POST",
      url: "bond_load_function.php",
      data: {
        check_vol_fun: 'check_vol_fun',
        symbol_id,
        cd_code
      },
      success: function(response) {
        $holdingDiv.show().html(response);
      }
    });
  } else {
    // Optional: clear if missing inputs
    $holdingDiv.hide().empty();
  }
}

$("#price").on('input', function () {
    let value = $(this).val();

    // Reset message
    $("#price_error").hide().text('');
    $(this).css('border-color', '');

    if (value === '') {
        $("#ytm_id").val('');
        $("#dirty_price").val('');
        $("#accur_int").val('');
        return;
    }

    // Rule 1: must be > 0
    if (parseFloat(value) <= 0) {
        $("#price_error").text("Price must be greater than 0").show();
        $(this).css('border-color', 'red');
        return;
    }

    // Rule 3: max 2 decimal places
    if (!/^\d+(\.\d{0,2})?$/.test(value)) {
        $("#price_error").text("Only up to 2 decimal places allowed").show();
        $(this).css('border-color', 'red');
        return;
    }

    // If valid
    $(this).css('border-color', 'green');

    let volume = $("#volume").val();
    let symbol_id = $("#symbol_id").val();
    // calculate ytm if price and symbol is not null
    if (value != '' && volume != '') {

      get_ytm_calculate(price, symbol_id).done(function(res) {
          if (res.status) {
              $("#ytm_id").val(res.data.ytm);
              $("#dirty_price").val(res.data.dirtyPrice);
              $("#accur_int").val(res.data.accrued);
          } else {
              alert(res.message);
          }

      }).fail(function() {
          alert("Error fetching YTM");
      });

    }
});

$("#volume").on('input', function () {
    let value = $(this).val();

    // Reset message
    $("#vol_error").hide().text('');
    $(this).css('border-color', '');

    if (value === '') {
        $("#ytm_id").val('');
        $("#dirty_price").val('');
        $("#accur_int").val('');
        return;
    }

    // Rule 1: must be > 0
    if (parseFloat(value) < 10) {
        $("#vol_error").text("Volume must be greater than 10").show();
        $(this).css('border-color', 'red');
        return;
    }

    // Rule 2: Check multiple of 10
    if (parseFloat(value) % 10 !== 0) {
        $("#vol_error").text("Volume must be a multiple of 10").show();
        $(this).css('border-color', 'red');
        return;
    }

    // If valid
    $(this).css('border-color', 'green');

    let price = $("#price").val();
    let symbol_id = $("#symbol_id").val();
    let side = $("#side").val();
    
    // calculate ytm if price and symbol is not null
    if (side === 'S') {
      if (value != '' && price != '') {
        get_ytm_calculate(price, symbol_id).done(function(res) {
            
            if (res.status) {
                $("#ytm_id").val(res.data.ytm);
                $("#dirty_price").val(res.data.dirtyPrice);
                $("#accur_int").val(res.data.accrued);
            } else {
                alert(res.message);
            }

        }).fail(function() {
            alert("Error fetching YTM");
        });
      }
    }
});

function get_ytm_calculate(price, symbol_id) {
    return $.ajax({
        type: 'POST',
        url: '../PROCESS/newton_raphson.php',
        data: {
            calculate_yield_to_maturity: 'calculate_yield_to_maturity',
            symbol_id: symbol_id,
            price: price
        },
        dataType: 'json'
    });
}

// submit bond order form
$("#submit_bond_order").on("click", function (e) {
    e.preventDefault();

    const cd_code = $("#cd_code").val();
    const symbol_id = $("#symbol_id").val();
    const side = $("#side").val();
    const price = $("#price").val();
    const volume = $("#volume").val();

    const fields = [
        { value: cd_code, message: "Required CD CODE" },
        { value: symbol_id, message: "Select Symbol" },
        { value: side, message: "Select Order Side" },
        { value: volume, message: "Enter Volume" }
    ];

    // Only require price if side is NOT 'B'
    if (side !== 'B') {
        fields.splice(3, 0, { value: price, message: "Enter Price" });
    }

    for (const field of fields) {
        if (!field.value) {
            showError(field.message);
            return;
        }
    }

    const order_type = $("#order_type").val();
    const operation = 'placing__bond__order';
    const accur_int = $("#accur_int").val();
    const dirty_price = $("#dirty_price").val();
    const ytm_id = $("#ytm_id").val();

    // continue with submit logic here
    if (confirm("Do you want to continue?")) {
        $.ajax({
          type: "POST",
          url: "../PROCESS/bond_trading_process.php",
          data: { 
            placing__bond__order: operation, cd_code, symbol_id, side, price, volume, order_type, accur_int, dirty_price, ytm_id
          },
          dataType: "html",
          success: function(response) {
            $("#message").show().html(response);
            showMessage();
          }
        });
    } else {
        return false;
    }
});

function showError(msg) {
    $("#message").html(
        `<div class='alert alert-danger' role='alert'>${msg}</div>`
    );
    showMessage();
}

$("#submit_reset_btn").on("click", function (e) {
    $("#cd_code").val('');
    $("#symbol_div_id").html('');
    $("#bond_details_id").html('');
    $("#holding_vol_div").html('');
});