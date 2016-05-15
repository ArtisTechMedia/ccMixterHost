<?

define('CC_STATUS_OK',    'ok');
define('CC_STATUS_ERROR', 'error');

require_once('cchost_lib/ccextras/cc-cart-table.inc');

class CCStatus {
  function CCStatus($status=CC_STATUS_OK,$errmsg='',$data=null) {
    $this->status = $status;
    $this->errmsg = $errmsg;
    $this->data   = $data;
    $this->cookie = $_COOKIE;
  }

  function ok() {
    return $this->status === CC_STATUS_OK;
  }
}

function _make_status($status, $errmsg='',$data=null) {
    return new CCStatus($status,$errmsg,$data);
}

function _make_ok_status($data='') {
  return _make_status(CC_STATUS_OK,'',$data);
}

function _make_err_status($msg) {
  return _make_status(CC_STATUS_ERROR,$msg);
}


?>