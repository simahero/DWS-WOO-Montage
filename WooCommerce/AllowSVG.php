<?php

add_filter('upload_mimes', 'dws_add_svg_file_types_to_uploads');
function dws_add_svg_file_types_to_uploads($file_types)
{
    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg+xml';
    $file_types = array_merge($file_types, $new_filetypes);
    return $file_types;
}
