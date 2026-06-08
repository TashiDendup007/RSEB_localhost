<form action="" method="POST" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#d0e4fe;">
          <button type="button" class="close" data-dismiss="modal">×</button>
          <h4 class="modal-title text-center">BUY ORDER</h4>
        </div>
        <div class="modal-body">
          <div id="loadingover" style="display: none;">
            <div id="loadingmsg" style="display: none;"></div>
          </div>

          <div id="orderMessageB"></div>
          <div class="row" ng-app="">
            <div class="col-lg-4 col-md-4 col-sm-12">
              <label for="cid">CD Code<font color="red">*</font></label>
              <input type="text" class="form-control" maxlength="10" style="text-transform:uppercase;" name="cid" id="cid" onchange="tots1(this.value);" required="">
              <input type="hidden" name="tp" id="tp" value="B">
              <input type="hidden" class="form-control" name="p_code" id="p_code" value="MEMBNBL">
              <input type="hidden" class="form-control" name="u_name" id="u_name" value="MEMBNBL001">
            </div>
            <div id="cd">
              <div class="col-lg-8 col-md-8 col-sm-12">
                <label>Client Details</label>
                <input type="text" class="form-control" value="TASHI DENDUP, 10904003674, U000004134" readonly="">

                <input type="hidden" id="b_commis" value="1.000">
                <input type="hidden" class="form-control" name="cd_code" id="cd_code" value="U000004134" readonly="">
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Security Type<font color="red">*</font>:</label>
                <select name="sec_type" id="sec_type" class="form-control" onchange="get_symbols_list(this.value, 'B');">
                  <option value="">-Security Type-</option><option value="OS">Ordinary Shares</option><option value="CB">Corporate Bonds</option><option value="GB">Government Bonds</option>
                </select>  
              </div>

              <div id="sym_list_div" style="">
              <div class="col-lg-8 col-md-8 col-sm-12" id="sy_div">
                <label>Symbol<font color="red">*</font></label>
                <select name="sy" id="sy" class="form-control" onchange="tots3(this.value);">
                  <option value="" selected="">-Select symbol-</option>
                  <option value="64">G030 (SUBORDINATED TERM DEBT OF T-BANK SERIES-I)</option>
                  <option value="80">BOB001 (Bank of Bhutan Ltd, Bond Series I)</option>
                </select>
              </div>
              </div>
              </div>
              <div id="cdd">
                  <div class="col-lg-4 col-md-4 col-sm-12" id="rfq_div_id">
                    <label>Symbol<font color="red">*</font></label>
                    <select name="order_type_id" id="order_type_id" class="form-control">
                      <option value="" selected="">-Select Order Type-</option>
                      <option value="OTC">Over The Counter</option>
                      <option value="RFQ">Request For Quote</option>
                    </select>
                  </div>
                  
                <div class="col-lg-4 col-md-4 col-sm-12" id="v_div">
                  <label for="buy_vol">Volume:<font color="red">*</font></label>
                  <input type="number" class="form-control" name="buy_vol" id="buy_vol" required="">
                  <span id="buyVolMsg" style="color:red;" class="help-block"></span>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12" id="p_div">
                  <label for="price">Price:<font color="red">*</font></label>
                  <input type="number" class="form-control" name="price" id="price" required="">
                </div>
                  <div class="col-lg-4 col-md-4 col-sm-12" id="ytm_div_id">
                    <label for="ytm_id">Yield To Maturity (YTM):</label>
                    <input type="number" class="form-control" name="ytm_id" id="ytm_id" readonly="">
                    <input type="hidden" class="form-control" name="dirty_price" id="dirty_price" readonly="">
                    <input type="hidden" class="form-control" name="accrued_interest" id="accrued_interest" readonly="">
                  </div>
                <div class="col-lg-4 col-md-4 col-sm-12" id="avl_amt_div_id">
                  <label>Available Amount (Nu.):</label> 
                  <input type="hidden" id="cap" value="150.00">
                  <input type="hidden" id="mp" value="1000.00">
                  <input type="hidden" id="cash" value="107874.00">
                  <input type="hidden" id="security_type" value="CB">
                  <input type="text" class="form-control" value="107,874.00" readonly="">
                </div>
                </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary submit" name="buysubmit" id="buysubmit" style="display: inline-block;"><i class="fa fa-database"></i> Submit</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
          <div class="col-sm-8">
            <span id="msg1" style="display:none; color:red;"></span><br>
            <span id="msg2" style="display:none; color:red;"></span>
            <span id="msg3" style="display:none; color:red;"></span>
          </div>
        </div>
      </div>
    </form>