<?php

// アプリケーション設定
define('appid', '**************************************');

// 検索ワード
$key = $_GET["t"];
$key = urldecode($key);

// ページ番号
$page = $_GET["p"];
if(empty($page)){
  $page = 1;
}

// 検索なし
if($key == ""){
  $baseurl = "http://auctions.yahooapis.jp/AuctionWebService/V2/categoryLeaf";
// 検索あり
}else{
  $baseurl = "http://auctions.yahooapis.jp/AuctionWebService/V2/search";
}

// リクエストのパラメータ作成
$params = array();
$params["appid"] = appid;
$params["output"] = "xml";
$params["sort"] = "bids";
$params["page"] = $page;
$params["category"] = "23336";

/* 0. 元となるリクエスト */
$base_request = "";
foreach ($params as $k => $v) {
  $base_request .= "&" . $k . "=" . $v;
}
$base_request = $baseurl . "?" . substr($base_request, 1);

/* 1. 「RFC 3986」形式でエンコーディング */
$base_request = "";
foreach ($params as $k => $v) {
  $base_request .= '&' . $k . '=' . rawurlencode($v);
  $params[$k] = rawurlencode($v);
}
$base_request = $baseurl . "?" . substr($base_request, 1);

if($key != ""){
  $base_request = $base_request . "&query=" . urlencode(mb_convert_encoding($key, "UTF-8", "auto"));
}

/* 2. XMLファイルを取得 */
$yahoo_xml = simplexml_load_string(file_get_contents($base_request));

/* 3. 結果を表示 */
foreach($yahoo_xml->Result->Item as $item) {

  // AuctionID
  $item_id = $item->AuctionID;

  // 商品名
  $item_title = $item->Title;

  // Seller
  $item_seller = $item->Seller->Id;

  // 商品へのURL
  $item_url = $item->AuctionItemUrl;

  // 画像のURL
  $item_img = $item->Image;

  // 価格
  $item_price = $item->CurrentPrice;
  if(empty($item_price)){
  
  }else{
    $item_price = number_format(intval($item_price));
  }

  // 入札数
  $item_bids = $item->Bids;

  $tag .= "<div class=\"col-sm-6 col-md-4\">\n";
  $tag .= "\t<div class=\"thumbnail\">\n";

  $tag .= "\t\t<h4 class=\"text-center\"><span class=\"label label-primary\">" . $item_id . "</span></h4>\n";

  $tag .= "\t\t<img src=\"" . $item_img . "\" alt=\"" . $item_title . "\" class=\"img-responsive\">\n";

  $tag .= "\t\t<div class=\"caption\">\n";

  $tag .= "\t\t\t<div class=\"row\">\n";
  $tag .= "\t\t\t\t<div class=\"col-md-6 col-xs-6\">\n";

  $tag .= "\t\t\t<h3>" . $item_title . "</h3>\n";

  $tag .= "\t\t\t\t</div>\n";
  $tag .= "\t\t\t\t<div class=\"col-md-6 col-xs-6\">\n";

  $tag .= "\t\t\t\t\t<h3><label>￥ " . $item_price . "</label></h3>\n\n";

  $tag .= "\t\t\t\t</div>\n";
  $tag .= "\t\t\t</div>\n";

  // セラーの表示
  if(!empty($item_seller)){
    $tag .= "\t\t\t\t\t<p><i class=\"fa fa-user\"></i> " . $item_seller . "</p>\n\n";
  }

  // 最終入札時間
  $item_end = $item->EndTime;
  $weeks = array("日", "月", "火", "水", "木", "金", "土");
  $item_time = explode("T", $item_end);
  $item_date = substr($item_time[0], 0, 4) . substr($item_time[0], 5, 2) . substr($item_time[0], 8, 2);
  $item_dt = substr($item_time[0], 0, 4) . "年" . substr($item_time[0], 5, 2) . "月" . substr($item_time[0], 8, 2) . "日(" . $weeks[date("w", strtotime($item_date))] . ") " . substr($item_time[1], 0, 5);
  if(!empty($item_end)){
    $tag .= "\t\t\t\t\t<p><i class=\"fa fa-hourglass-end\"></i> " . $item_dt . "</p>\n\n";
  }

  $tag .= "\t\t\t\t\t<p><i class=\"fa fa-users\"></i> " . $item_bids . "</p>\n\n";

  $tag .= "\t\t\t<div class=\"row\">\n";
  $tag .= "\t\t\t\t<div class=\"col-md-6\">\n";
  $tag .= "\t\t\t\t\t<a class=\"btn btn-primary btn-product\" href=\"" . $item_img . "\" data-gallery><i class=\"fa fa-picture-o\"></i> Sample</a>\n";
  $tag .= "\t\t\t\t</div>\n";
  $tag .= "\t\t\t\t<div class=\"col-md-6\">\n";
  $tag .= "\t\t\t\t\t<a class=\"btn btn-success btn-product\" href=\"" . $item_url . "\" target=\"_blank\"><span class=\"glyphicon glyphicon-eye-open\"></span> View</a>\n";
  $tag .= "\t\t\t\t</div>\n";
  $tag .= "\t\t\t</div>\n\n";

  $tag .= "\t\t</div>\n\n";
  $tag .= "\t</div>\n\n";
  $tag .= "</div>\n\n";

}

// 全ページ数を取得する
$totalpages = $yahoo_xml->attributes()->totalResultsAvailable;
$tpage = ceil($totalpages/20);

if($totalpages == "0" or $totalpages == ""){
$tag = <<< EOM
<div class="bs-component">
<div class="alert alert-dismissible alert-danger">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<strong>Oh snap!</strong> <a href="./" class="alert-link">Change a few things up</a> and try submitting again.
</div>
</div>
EOM;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>サンプル - ヤフオク！</title>
<meta name="description" content="ヤフオクの出品中を一覧表示するツールです。" />
<meta name="keywords" content="オークション" />
<meta name="author" content="Akira Mukai" />
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="//blueimp.github.io/Gallery/css/blueimp-gallery.min.css">
<link rel="stylesheet" href="./css/bootstrap-image-gallery.min.css">
<style type="text/css">
  body { padding-top: 80px; }
  @media ( min-width: 768px ) {
    #banner {
      min-height: 300px;
      border-bottom: none;
    }
    .bs-docs-section {
      margin-top: 8em;
    }
    .bs-component {
      position: relative;
    }
    .bs-component .modal {
      position: relative;
      top: auto;
      right: auto;
      left: auto;
      bottom: auto;
      z-index: 1;
      display: block;
    }
    .bs-component .modal-dialog {
      width: 90%;
    }
    .bs-component .popover {
      position: relative;
      display: inline-block;
      width: 220px;
      margin: 20px;
    }
    .nav-tabs {
      margin-bottom: 15px;
    }
    .progress {
      margin-bottom: 10px;
    }
    .btn-product{
      width: 100%;
    }
    #adv-search {
      width: 500px;
      margin: 0 auto;
    }
    .dropdown.dropdown-lg {
        position: static !important;
    }
    .dropdown.dropdown-lg .dropdown-menu {
        min-width: 500px;
    }

  }
  .dropdown.dropdown-lg .dropdown-menu {
      margin-top: -1px;
      padding: 6px 20px;
  }
  .input-group-btn .btn-group {
      display: flex !important;
  }
  .btn-group .btn {
      border-radius: 0;
      margin-left: -1px;
  }
  .btn-group .btn:last-child {
      border-top-right-radius: 4px;
      border-bottom-right-radius: 4px;
  }
  .btn-group .form-horizontal .btn[type="submit"] {
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
  }
  .form-horizontal .form-group {
      margin-left: 0;
      margin-right: 0;
  }
  .form-group .form-control:last-child {
      border-top-left-radius: 4px;
      border-bottom-left-radius: 4px;
  }

  .btn.btn-circle { border-radius: 50px; }
  .btn.btn-outline { background-color: transparent; }

  .btn-outline-rounded{
      padding: 10px 40px;
      margin: 20px 0;
      border: 2px solid transparent;
      border-radius: 25px;
  }

  .btn.green{
      background-color:#5cb85c;
      /*border: 2px solid #5cb85c;*/
      color: #ffffff;
  }
</style>

<!--[if lt IE 9]>
<script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

</head>
<body>
<header>
  <div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <a href="./" class="navbar-brand"><i class="fa fa-gavel"></i> ヤフオク！</a>
        <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      </div>
      <div class="navbar-collapse collapse" id="navbar-main">
        <ul class="nav navbar-nav navbar-right">
          <li>
            <a class="btn btn-default btn-outline btn-circle"  data-toggle="collapse" href="#nav-collapse3" aria-expanded="false" aria-controls="nav-collapse3">Search</a>
          </li>
        </ul>
        <div class="collapse nav navbar-nav nav-collapse" id="nav-collapse3">
            <form class="navbar-form navbar-right" role="search" action="./sample.php">
            <div class="form-group">
            <input type="text" class="form-control" placeholder="Search" name="t" value="<?php echo $key; ?>">
            </div>
            <button class="btn btn-primary" type="submit"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
            </form>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="container">

  <div class="row">
    <div class="col-lg-12">
      <div class="page-header">
        <h1 id="forms"><i class="fa fa-gavel"></i> ヤフオク！カンタン検索</h1>
      </div>
    </div>
  </div>

  <div class="row">

  <div id="content">
  <div class="article">

<?php

echo $tag;

?>

  </div>
  <!-- /.article -->

<?php

$t = urlencode($key);

if($page +1 <= $tpage){
  $next = $page + 1;
  echo <<< EOT
  <div class="navigation text-center">
  <a class="btn btn-primary btn-sm" href="sample.php?t={$t}&p={$next}"><span class="glyphicon glyphicon-refresh"></span> さらに読み込む</a>
  </div>
EOT;
}
?>

  </div>
  <!-- /.content -->

  </div>
  <!-- /.row -->

  <hr>

  <!-- Footer -->
  <footer>
  <div class="row">
  <div class="col-lg-12">
  <p><!-- Begin Yahoo! JAPAN Web Services Attribution Snippet -->
  <a href="http://developer.yahoo.co.jp/about">Webサービス by Yahoo! JAPAN</a>
  <!-- End Yahoo! JAPAN Web Services Attribution Snippet --><br><br>
  </p>
  </div>
  <!-- /.col-lg-12 -->
  </div>
  <!-- /.row -->
  </footer>

</div>

<!-- The Bootstrap Image Gallery lightbox, should be a child element of the document body -->
<div id="blueimp-gallery" class="blueimp-gallery">
    <!-- The container for the modal slides -->
    <div class="slides"></div>
    <!-- Controls for the borderless lightbox -->
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
    <!-- The modal dialog, which will be used to wrap the lightbox content -->
    <div class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body next"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left prev">
                        <i class="glyphicon glyphicon-chevron-left"></i>
                        Previous
                    </button>
                    <button type="button" class="btn btn-primary next">
                        Next
                        <i class="glyphicon glyphicon-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="./js/jquery.infinitescroll.min.js"></script>
<script src="//blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
<script src="./js/bootstrap-image-gallery.min.js"></script>

<script>
$(function(){
  $(".dropdown").hover(
  function() {
    $('.dropdown-menu', this).stop( true, true ).slideDown("fast");
    $(this).toggleClass('open');
  },
  function() {
    $('.dropdown-menu', this).stop( true, true ).slideUp("fast");
    $(this).toggleClass('open');
  });

  $('#content').infinitescroll({
    navSelector  : ".navigation",
    nextSelector : ".navigation a",
    itemSelector : ".article"
  },function(newElements) {
      $(newElements).hide().delay(100).fadeIn(600);
      $(".navigation").appendTo("#content").delay(300).fadeIn(600);
  });

  $('#content').infinitescroll('unbind');
  $(".navigation a").click(function(){
    $('#content').infinitescroll('retrieve');
    return false;
  });

});
</script>

</body>
</html>
