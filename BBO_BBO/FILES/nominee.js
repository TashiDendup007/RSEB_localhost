function get_application_name(cid) {
  showLoading();
  if (cid != '') {
    $.ajax({
        type: "POST", 
        url: "../PROCESS/process.php", 
        data: { 
          cid_no: cid, 
          get__applicant__dtls : 'get__applicant__dtls' 
        },
        dataType: 'json',
        success: function(response) {
          hideloading();
          $("#applicant_dtls_id").html(response.message);
          $("#save__nominee__dtls").attr("disabled", false);
          
          if (response.status == 200) {
            $("#nominee_table_id").show();
            $("#modal_footer_id").show();
            $("#tbody_nominee_id").html(response.tbody);
          } else if (response.status == 300) {
            $("#nominee_table_id").show();
            $("#modal_footer_id").show();
            $("#tbody_nominee_id").html(response.tbody);
          } else {
            $("#nominee_table_id").hide();
            $("#modal_footer_id").hide();
          }
        } 
    });
  } else {
    hideloading();
    $("#applicant_dtls_id").html('');
    $("#nominee_table_id").hide();
    $("#table_add_remove_id").hide();
    $("#modal_footer_id").hide();
  }
}

$("#save__nominee__dtls").click( function () {
  showLoading();
  let app_cid_no = $("#applicant_cid_no").val().trim();
  let table = document.getElementById("nominee_table_id");
  let rows = table.querySelectorAll("tbody tr");
  let nominees = [];

  // Loop through all rows except the last one (which has buttons)
  let total_percentage = 0;
  let cidSet = new Set();

  if (app_cid_no == '') {
      hideloading();
      alert(`Applicant CID No. cannot be empty`);
      return;
  }

  for (let i = 0; i < rows.length - 1; i++) {
      let row = rows[i];
      let name = row.querySelector('input[name="nom_name[]"]')?.value.trim();
      let cid = row.querySelector('input[name="nom_cid[]"]')?.value.trim();
      let relation = row.querySelector('select[name="nom_relation[]"]')?.value.trim();
      let secType = row.querySelector('select[name="nom_sec_type[]"]')?.value.trim();
      let percent = row.querySelector('input[name="nom_percent[]"]')?.value.trim();

      // Validation
      if (!name || !cid || !relation || !secType || !percent) {
          hideloading();
          alert(`Please fill in all fields for nominee row ${i + 1}`);
          return;
      }

      if (cid.length != 11 || isNaN(cid)) {
          hideloading();
          alert(`CID Number must be exactly 11 digits in row ${i + 1}`);
          return;
      }

      if (cid === app_cid_no) {
          hideloading();
          alert(`Nominee CID cannot match Applicant CID in row ${i + 1}`);
          return;
      }

      if (cidSet.has(cid)) {
          hideloading();
          alert(`Duplicate nominee CID found in row ${i + 1}`);
          return;
      }
      cidSet.add(cid);

      let percentValue = parseFloat(percent);
      if (isNaN(percentValue)) {
          hideloading();
          alert(`Invalid percentage value in row ${i + 1}`);
          return;
      }
      total_percentage += percentValue;

      // Store validated nominee
      nominees.push({
          name: name,
          cid: cid,
          relation: relation,
          secType: secType,
          percent: parseFloat(percent)
      });
  }

  if (total_percentage !== 100) {
    hideloading();
    alert("Total Ownership % should be 100");
    return;
  } else {
    // console.log("Nominees:", nominees);
      if (confirm("Double-check your nominee details before submitting. Incorrect entries will replace the existing nominees if any.")) {
        $.ajax({
            type: "POST", 
            url: "../PROCESS/process.php", 
            data: { 
              nominees: JSON.stringify(nominees), 
              appli_cid_no : app_cid_no, 
              save__nominee_dtls : 'save__nominee_dtls' 
            },
            dataType: 'html',
            success: function(response) {
                hideloading();
                $("#save__nominee__dtls").attr("disabled", true);
                $("#nominee_message").html(response);
            } 
        });
      } else {
        hideloading();
        return;
      }
  }
});

function add_nominee() {
    const table = document.getElementById("nominee_table_id").getElementsByTagName("tbody")[0];
    const nomineeRows = table.rows.length - 1;

    if (nomineeRows >= 3) {
        alert("You can only add up to 3 nominees.");
        return;
    }

    const templateRow = table.rows[0];
    const newRow = templateRow.cloneNode(true);

    // Reset inputs in the new row
    newRow.querySelectorAll('input, select').forEach(input => input.value = '');

    newRow.cells[0].textContent = nomineeRows + 1;
    table.insertBefore(newRow, table.rows[table.rows.length - 1]);
}

function remove_nominee() {
    const table = document.getElementById("nominee_table_id").getElementsByTagName("tbody")[0];
    const totalRows = table.rows.length;

    if (totalRows > 2) { // 1 nominee row + 1 control row minimum
        table.deleteRow(totalRows - 2); // Remove last nominee row
    }
}