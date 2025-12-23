<!-- breadcrumb -->
<div class="breadcrumb-header justify-content-between">
    <div class="left-content">
        <span class="main-content-title mg-b-0 mg-b-lg-1">
            {{ $title ?? 'الصفحة' }}
        </span>
    </div>
    <div class="justify-content-center mt-2">
        <ol class="breadcrumb">
            @if(!empty($parent))
                <li class="breadcrumb-item fs-15">
                    <a href="{{ $parent_url ?? 'javascript:void(0);' }}">{{ $parent }}</a>
                </li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">
                {{ $title ?? '' }}
            </li>
        </ol>
    </div>
</div>
<!-- /breadcrumb -->

