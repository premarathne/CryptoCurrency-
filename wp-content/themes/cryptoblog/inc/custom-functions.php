<?php

//GET HEADER TITLE/BREADCRUMBS AREA
if (!function_exists('cryptoblog_header_title_breadcrumbs')) {
    function cryptoblog_header_title_breadcrumbs(){

        $html = '';
        $html .= '<div class="header-title-breadcrumb relative">';
            $html .= '<div class="header-title-breadcrumb-overlay text-center">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-6 text-left">';
                                        if (is_single()) {
                                            $html .= '<h1>'.esc_html__( 'Blog', 'cryptoblog' ) . get_search_query().'</h1>';
                                        }elseif (is_page()) {
                                            $html .= '<h1>'.get_the_title().'</h1>';
                                        }elseif (is_search()) {
                                            $html .= '<h1>'.esc_html__( 'Search Results for: ', 'cryptoblog' ) . get_search_query().'</h1>';
                                        }elseif (is_author() || is_archive() || is_tag() || is_category()) {
                                            $html .= '<h1>'.get_the_archive_title().'</h1>';
                                        }elseif (is_home()) {
                                            $html .= '<h1>'.esc_html__( 'From the Blog', 'cryptoblog' ).'</h1>';
                                        }else {
                                            $html .= '<h1>'.get_the_title().'</h1>';
                                        }
                          $html .= '</div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <ol class="breadcrumb text-right">'.cryptoblog_breadcrumb().'</ol>                    
                                    </div>
                                </div>
                            </div>
                        </div>';

        $html .= '</div>';
        $html .= '<div class="clearfix"></div>';

        return $html;
    }
}

?>