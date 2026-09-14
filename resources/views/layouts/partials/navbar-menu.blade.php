<div class="navbar-expand-md">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
            <div class="container-xl">
                <ul class="navbar-nav">
                    @foreach ($menu as $item)
                        <x-menu-item :item="$item" />
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
