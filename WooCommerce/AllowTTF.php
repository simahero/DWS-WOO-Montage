<?php

add_filter('upload_mimes', 'dws_add_ttf_file_types_to_uploads');
function dws_add_ttf_file_types_to_uploads($file_types)
{
    $new_filetypes = array();
    $new_filetypes['ttf'] = 'application/x-font-ttf';
    $file_types = array_merge($file_types, $new_filetypes);
    return $file_types;
}
