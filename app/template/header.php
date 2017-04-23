	<?php 
	
	require_once(ROOT_PUBLIC.G_ANALYTICS);
	
	
	if(!$loggedIn)
	{
		echo "<link rel='stylesheet' href='".PUBLIC_URL."resources/css/login-register-modals.css' type='text/css'/>";
		echo "<script type='text/javascript' language='javascript' src='".PUBLIC_URL."resources/js/login-register.js'></script>";
		
		require_once(ROOT."app/template/login-modal.php");
		require_once(ROOT."app/template/register-modal.php");
	}
	
	
 	/*function endsWith($haystack, $needle)
	{ return $needle === "" || substr($haystack, -strlen($needle)) === $needle; }*/
	
	
	
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
                            echo "<a href='".PUBLIC_URL."juegos/?tag=".$category["tag_name"]."'>".ucfirst($category["tag_name"])."</a> <span style='color:#666'>(".$category["product_count"].")</span><br/>";
                        }
                        ?>
                    </div>
                    <div style="float:right">
						<?php
                        $tag_query = mysqli_query($con, "SELECT * FROM `game_categories` ORDER BY `product_count` DESC LIMIT 20,20");
                        while($category = mysqli_fetch_assoc($tag_query)) {
                            echo "<a href='".PUBLIC_URL."juegos/?tag=".$category["tag_name"]."'>".ucfirst($category["tag_name"])."</a> <span style='color:#666'>(".$category["product_count"].")</span><br/>";
                        }
                        ?>
                    </div>
                </div>

			</div>
		</div>
        
        <div class="nav-search-form form-group has-feedback">
            <form action="<?php echo PUBLIC_URL . "juegos/" ?>" name="searchform" method="get">
                <input type="text" name="q" class="form-control" id="search-products-input" placeholder="Buscar juegos..." autocomplete="off" spellcheck="false" <?php 
				if(isset($_GET["q"])) { ?> value="<?php echo htmlspecialchars($_GET["q"]); ?>" <?php } ?> />
                <span class="glyphicon glyphicon-search form-control-feedback" aria-hidden="true" onclick="document.searchform.submit();"></span>	
            </form>
            <div id="search-autocomplete-box">
            	<span id="search-autocomplete-spinner"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></span>
                <div></div>
            </div>
        </div>
        
        <?php
		if(!$loggedIn) {
			?>
            <div class="nav-login-options">
                <a href="javascript:void(0);" id="nav-login-btn" data-toggle="modal" data-target="#login-modal">Iniciar sesión</a>&nbsp;&nbsp;ó&nbsp;
                <button type="button" class="btn btn-default btn-primary" id="nav-register-btn" data-toggle="modal" data-target="#register-modal">Registrarse</button>
            </div>
            <?php	
		} else {
			?>
			<div class="nav-user-options">
				<div class="btn-group">
					<a href="<?php echo PUBLIC_URL . "cuenta/pedidos/"; ?>" class="btn btn-primary">Mis pedidos</a>
					<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
					</button>
					<ul class="dropdown-menu" role="menu">
                       	<div class="user_dropdown_email"><?php echo $userData["email"]; ?></div>
                        <li><a href="<?php echo PUBLIC_URL."cuenta/libreria/"; ?>">Mi librería</a></li>
						<li><a href="<?php echo PUBLIC_URL."cuenta/configuracion/"; ?>">Mi cuenta</a></li>
						<li class="divider"></li>
						<li><a href="<?php echo PUBLIC_URL . "cuenta/logout.php?redir=".urlencode($_SERVER["REQUEST_URI"]); ?>">Cerrar sesión</a></li>
					</ul>
				</div> 
			</div>
            <?php
		}
		?>

		
		
		
		<?php
		/*
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
		*/
		?>

    </div>


    <div class="header">
		<a href="<?php echo PUBLIC_URL; ?>"><img class="mainlogo" src="<?php echo PUBLIC_URL; ?>global_design/img/header-logo.png" alt="steambuy logo" /></a>
        <div style="float:right">
            <div class="header_socialbtns">
                <a href="http://facebook.com/steambuy" target="_blank"><i class="fa fa-facebook-square"></i></a><a href="http://twitter.com/steambuy" target="_blank"><i class="fa fa-twitter-square"></i></a><a href="http://plus.google.com/+SteamBuyAR" target="_blank"><i class="fa fa-google-plus-square"></i></a>
            </div>
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
	</div>
    
