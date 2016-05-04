@extends('templates.master')

@section('content')

@include('partials.archive-filters')

<div class="container main-container">
    @include('partials.breadcrumbs')

    <div class="grid">
        @if (get_field('archive_' . sanitize_title(get_post_type()) . '_show_sidebar_navigation', 'option'))
            @include('partials.sidebar-left')
        @endif

        <?php
            $cols = 'grid-md-12';
            if (is_active_sidebar('right-sidebar') && get_field('archive_' . sanitize_title(get_post_type()) . '_show_sidebar_navigation', 'option')) {
                $cols = 'grid-md-8 grid-lg-6';
            } elseif (is_active_sidebar('right-sidebar') || get_field('archive_' . sanitize_title(get_post_type()) . '_show_sidebar_navigation', 'option')) {
                $cols = 'grid-md-12 grid-lg-9';
            }
        ?>

        <div class="{{ $cols }}">
            <div class="grid">
                @while(have_posts())
                    {!! the_post() !!}

                    @if (in_array($template, array('full', 'compressed', 'collapsed')))
                        <div class="grid-xs-12">
                            @include('partials.blog.type.post-' . $template)
                        </div>
                    @else
                        @include('partials.blog.type.post-' . $template)
                    @endif
                @endwhile
            </div>

            <div class="grid">
                <div class="grid-sm-12 text-center">
                    {!!
                        paginate_links(array(
                            'type' => 'list'
                        ))
                    !!}
                </div>
            </div>
        </div>

        @include('partials.sidebar-right')
    </div>
</div>

@stop
