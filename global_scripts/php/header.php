	<?php 
	
	require_once("g_analytics.php"); 
	
	function endsWith($haystack, $needle)
	{ return $needle === "" || substr($haystack, -strlen($needle)) === $needle; }
	?>
    
	<div class="nav_bar">
        
        <div class="btn-group nav-explore-dropdown">
  			<button type="button" class="btn btn-default dropdown-toggle explore-dropdown" data-toggle="dropdown">Explorar <span class="caret"></span></button>
			<div class="dropdown-menu explore-dropdown-content" role="menu">
            	<h3 style="margin-bottom:10px;">Categorías</h3>
            	<div>
					<div style="float:left">
						<?php
                        $tag_query = mysqli_query($con, "SELECT * FROM `game_categories` ORDER BY `product_count` DESC LIMIT 0,20");
                        while($category = mysqli_fetch_assoc($tag_query)) {
                            echo "<a href='".ROOT_LEVEL."juegos/?tag=".$category["tag_name"]."'>".ucfirst($category["tag_name"])."</a> <span style='color:#666'>(".$category["product_count"].")</span><br/>";
                        }
                        ?>
                    </div>
                    <div style="float:right">
						<?php
                        $tag_query = mysqli_query($con, "SELECT * FROM `game_categories` ORDER BY `product_count` DESC LIMIT 20,20");
                        while($category = mysqli_fetch_assoc($tag_query)) {
                            echo "<a href='".ROOT_LEVEL."juegos/?tag=".$category["tag_name"]."'>".ucfirst($category["tag_name"])."</a> <span style='color:#666'>(".$category["product_count"].")</span><br/>";
                        }
                        ?>
                    </div>
                </div>

			</div>
		</div>
        
        <div class="nav-search-form">
            <form action="<?php echo ROOT_LEVEL . "juegos/" ?>" method="get">
                <input type="text" name="q" class="form-control" placeholder="Buscar juegos..." <?php if(isset($_GET["q"])) { ?> value="<?php echo htmlspecialchars($_GET["q"]); ?>" <?php } ?> />	
                <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span></button>
            </form>
        </div>
        
        <div class="nav-toolbtn">
			<?php 
            if($admin == true) {
                ?>
				<div class="btn-group">
					<a href="<?php echo ROOT_LEVEL . "admin/"; ?>" class="btn btn-primary">Panel de admin</a>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="<?php echo ROOT_LEVEL."admin/pedidos.php?type=1"; ?>">Pedidos activos</a></li>
                        <li><a href="<?php echo ROOT_LEVEL."admin/pedidos.php?type=2"; ?>">Pedidos concretados</a></li>
                        <li><a href="<?php echo ROOT_LEVEL."admin/pedidos.php?type=3"; ?>">Pedidos cancelados</a></li>
                        <li class="divider"></li>
                        <li><a href="<?php echo ROOT_LEVEL."admin/products/"; ?>">Modificar catálogo</a></li>
                        <li class="divider"></li>
                        <li><a href="<?php echo ROOT_LEVEL . "admin/logout.php?redir=".urlencode($_SERVER["REQUEST_URI"]); ?>">Cerrar sesión</a></li>
                    </ul>
                </div>
                <?php			
            } else {
            ?>
				<div class="btn-group">
                    <a href="<?php echo ROOT_LEVEL."pedido/"; ?>" class="btn btn-primary">Mi pedido</a>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="<?php echo ROOT_LEVEL."informar/"; ?>">Informar pago</a></li>
                        <li><a href="<?php echo ROOT_LEVEL."cancelar/"; ?>">Cancelar pedido</a></li>
                    </ul>
                </div>
            <?php	
            }
			?>
        </div>

    </div>


    <div class="header">
		<a href="<?php echo ROOT_LEVEL; ?>"><img class="mainlogo" src="<?php echo ROOT_LEVEL; ?>global_design/img/header-logo.png" alt="steambuy logo" /></a>
        <div style="float:right">
            <div class="header_socialbtns">
                <a href="http://facebook.com/steambuy" target="_blank"><i class="fa fa-facebook-square"></i></a><a href="http://twitter.com/steambuy" target="_blank"><i class="fa fa-twitter-square"></i></a><a href="http://plus.google.com/+SteamBuyAR" target="_blank"><i class="fa fa-google-plus-square"></i></a>
            </div>
            <div class="xmass"></div>
            <div id="fb-root"></div>
            <script>(function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = "//connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v2.0";
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>
            <div class="fblike">
                <div class="fb-like" data-href="http://facebook.com/steambuy" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
            </div>
        </div>
        <div class="xmass"></div>
	</div>
    
