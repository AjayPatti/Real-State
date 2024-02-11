
<header class="main-header">
        <!-- Logo -->
        <a href="dashboard.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>WCC</b></span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>WCC</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <!-- Messages: style can be found in dropdown.less-->
              
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="/<?php echo $host_name; ?>img/logo.png" class="user-image" alt="Image" />
                  <?php $user = mysqli_fetch_assoc(mysqli_query($conn,"select id, name, email, mobile, login_type from kc_login where id = '".$_SESSION['login_id']."'")); ?>
                  <span class="hidden-xs"><?php echo $user['name']; ?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="/<?php echo $host_name; ?>img/logo.png" class="img-circle" alt="Image" />
                    <p>
                      WCC
                      <small>Member since Nov. 2024</small>
                    </p>
                  </li>
                  <!-- Menu Body -->

                  <!-- Menu Footer-->
                  <li class="user-footer">
                    
                    <div class="pull-left">
                      <a href="/<?php echo $host_name; ?>change_password.php" class="btn btn-default btn-flat">Change Password</a>
                    </div>
                    
                    <div class="pull-right">
                      <a href="/<?php echo $host_name; ?>logout.php" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
              <!-- Control Sidebar Toggle Button -->
              <?php /*
              <li>
                <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
              </li>
              */ ?>
            </ul>
          </div>
        </nav>
        
      </header>
