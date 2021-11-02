<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <span class="brand-text text-center font-weight-light" style="margin-left: 50px">WEB GATEWAY</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">


        <!-- SidebarSearch Form -->
        <!-- href be escaped -->
        <!-- <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div> -->

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => [
                    ['label' => 'Dashboard', 'url' => ['site/index'], 'icon' => 'tachometer-alt'],
                    [
                        'label' => 'Company Management',
                        'icon' => 'file',
                        'badge' => '<span class="right badge badge-info"></span>',
                        'items' => [
                            ['label' => 'New company', 'url' => ['webvfd/company/create'], 'icon' => 'file-signature text-green'],
                            ['label' => 'Manage Companies', 'url' => ['webvfd/company/index'], 'icon' => 'fas fa-cog text-yellow'],
                        ]
                    ],


                    [
                        'label' => 'Sales',
                        'icon' => 'fas fa-chart-line',
                        'badge' => '<span class="right badge badge-info"></span>',
                        'items' => [
                            ['label' => 'Incoming sales', 'url' => ['webvfd/incoming-sales-data/index'], 'icon' => ''],


                        ]
                    ],

                    [
                        'label' => 'Report',
                        'icon' => 'fas fa-chart-line',
                        'badge' => '<span class="right badge badge-info"></span>',
//                        'items' => [
//                            ['label' => 'Rent', 'url' => ['report/plan'], 'icon' => ''],
//                            ['label' => 'Expenses', 'url' => ['report/plan'], 'icon' => ''],
//                            ['label' => 'Properties', 'url' => ['report/rent'], 'icon' => ''],
//                            ['label' => 'Plan', 'url' => ['report/rent'], 'icon' => ''],
//
//                        ]
                    ],

                    [
                        'label' => 'System',
                        'icon' => 'fas fa-cogs',
                        'badge' => '<span class="right badge badge-info"></span>',
                        'items' => [
                            ['label' => 'User', 'url' => ['user/index'], 'icon' => 'user-friends'],


                        ]
                    ],


                ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>