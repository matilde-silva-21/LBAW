<header>
    <nav class="navbar navbar-expand-xxl">
        <div class="container">

            <a class="navbar-brand text-white " href="{{route('home.show')}}"><i class="fa fa-solid fa-camera-retro fa-lg mr-2"></i> RE-EVENT </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nvbCollapse" aria-controls="nvbCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nvbCollapse">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item pl-1 justify-content-end">
                        <a class="nav-link" href="{{route('home.show')}}"><i class="fa fa-home fa-fw mr-1"></i> Home </a>
                    </li>
                    <li class="nav-item pl-1">
                        <a class="nav-link" href="{{route('faq.index')}}"><i class="fa fa-info-circle fa-fw mr-1"></i> FAQ </a>
                    </li>
                    <li class="nav-item pl-1">
                        <a class="nav-link" href="{{route('aboutUs.index')}}"><i class="fa fa-phone fa-fw fa-rotate-180 mr-1"></i> About Us </a>
                    </li>
                    <!-- Button trigger modal -->
                    <li class="nav-item pl-1" data-toggle="modal" data-target="#basicModal">
                        <a class="nav-link"><i class="fa fa-fw fa-search mr-1"></i> Search </a>
                    </li>
                    @if (Auth::check())

                    <li class="nav-item">
                        <a id="notification_text" class="nav-link" href="{{route('user.show', Auth::user()->userid)}}"> </a>
                    </li>
                    <li class="nav-item pl-1">
                        <a class="nav-link" id="__nav-bar-user"  href="{{route('user.show', Auth::user()->userid)}}"><i class="fa fa-user fa-fw mr-1"></i> {{Auth::user()->name}} </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="__nav-bar-logout" href="{{route('logout')}}"><i class="fa fa-sign-in-alt fa-fw mr-1"></i> Logout </a>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link" id="__nav-bar-login" href="{{route('login')}}"><i class="fa fa-user fa-fw mr-1"></i> Login </a>
                    </li>
                    @endif


                </ul>
            </div>
        </div>
    </nav>

    <!-- Modal -->

    <!-- Full screen modal -->
    <div class="modal fade" id="basicModal" tabindex="-1" role="dialog" aria-labelledby="basicModal" data-backdrop="false" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-content-box" style="height: 18rem;">

                <div class="form-group">
                    <input type="text" class="form-controller" id="searchInput" name="search">
                </div>
                <table class="table table-bordered table-hover" style="margin-top:1rem;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>City</th>
                        </tr>
                    </thead>
                    <tbody id="table-res">
                    </tbody>
                </table>
                <button id="close-modal-button" data-dismiss="modal"></button>

            </div>
        </div>
    </div>

</header>
