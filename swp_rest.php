<?php
/**
 * Plugin Name: SWP - REST API
 * Description: Pull entries from an external site
 * Version: 1.1
 * Author: Jake Almeda
 * Author URI: http://smarterwebpackages.com/
 * Network: true
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class SWPRestAPIFunction {

    //REST MAIN FUNCTION
    public function swp_pull_contents_func( $params ) {

        // RETRIEVE ATTRIBUTE(S)
        extract(shortcode_atts(array(
                    'site'          => 'site',
                    'post_type'     => 'post_type',
                    'id'            => 'id',
                    'field'         => 'field',
                    'size'          => 'size',
                ), $params));

        // VALIDATE
        if( $this->swp_validate_atts( $site, 'site' ) && $this->swp_validate_atts( $post_type, 'post_type' ) ) {

            if( $this->swp_validate_atts( $id, 'id' ) && $this->swp_validate_atts( $field, 'field' ) ) {
                // display specific entry
                return $this->swp_get_field( $site, $post_type, $id, $field, $size );
            } else {
                return "Please specify the ID and the field you want to retrieve.";
            }

        } else {
            return "Please specify the target site (URL) and/or the post type.";
        }

    }

    // GET REST DATA
    private function swp_get_field( $site, $post_type, $id, $field, $size ) {

        /*
            https://staging.indrapr.com/wp-json/wp/v2/news
            https://staging.indrapr.com/wp-json/wp/v2/news/18032
        */
        if( $id ) {
            $target = file_get_contents( rtrim( $site, "/" ).'/wp-json/wp/v2/'.$post_type.'/'.$id );
        } else {
            $target = file_get_contents( rtrim( $site, "/" ).'/wp-json/wp/v2/'.$post_type );
        }

        $array = json_decode( $target, TRUE, 512 );
        foreach( $array as $key => $value ) {
            //echo $key.' = '.$value.'<br /><br /><br />';
            if( $key == 'acf' ) {
                
                foreach ( $value as $key2 => $value2 ) {
                    //echo '<h1>'.$key2.' == '.$field.'</h1>';
                    if( $key2 == $field ) {

                        if( is_array( $value2 ) ) {
                            //echo '<h1>'.$value2['id'].'</h1>';
                            //var_dump( $value2 ); echo '<br /><br />';

                            //echo '<h1>'.$key2.'</H1>';
                            if( $key2 == 'media_youtube' ) {
                                return $value2[ 'url' ];
                            }

                            // media_photo
                            if( $key2 == 'media_photo' ) {
                                if( $this->swp_validate_atts( $size, 'size' ) ) {
                                    return '<img src="'.$value2[ 'sizes' ][ $size ].'" /> ';
                                } else {
                                    // load default - featured-icon
                                    return '<img src="'.$value2[ 'sizes' ][ 'featured-icon' ].'" /> ';
                                }
                            }

                            // media_gallery
                            if( $key2 == 'media_gallery' ) {
                                foreach ( $value2 as $key3 => $value3 ) {

                                    //echo $key3.' => '.$value3[ 'id' ]; echo '<br /><br />';
                                    
                                    //var_dump( $value3 );
                                    /*
                                    echo '<strong>URL:</strong> '.$value3['url'];
                                    echo '<br />';
                                    echo '<strong>Thumbnail</strong>: '.$value3['sizes']['thumbnail'].'<br />';
                                    echo '<strong>Medium</strong>: '.$value3['sizes']['medium'].'<br />';
                                    echo '<strong>Medium-Large</strong>: '.$value3['sizes']['medium_large'].'<br />';
                                    echo '<strong>Large</strong>: '.$value3['sizes']['large'].'<br />';
                                    echo '<strong>Featured-Icon</strong>: '.$value3['sizes']['featured-icon'].'<br />';
                                    
                                    echo '<br /><br />';*/
                                    if( $value3 ) {
                                        //echo '<h1>'.$key2.' | '.$key3.'</h1>';var_dump($value3);
                                    }
                                    // validate $size | thumbnail, medium, medium_large, large, featured-icon
                                    if( $this->swp_validate_atts( $size, 'size' ) ) {
                                        $return .= '<img src="'.$value3[ 'sizes' ][ $size ].'" /> ';
                                    } else {
                                        // load default - featured-icon
                                        $return .= '<img src="'.$value3[ 'sizes' ][ 'featured-icon' ].'" /> ';
                                    }
                                }

                                return $return;
                            }

                        } else {

                            if( is_array( $value2 ) ) {
                                return $value2[ 'rendered' ];
                            } else {
                                return $value2;
                            }

                        }

                    }

                }
                

            } else {
                
                if( $key == $field ) {
                    
                    if( is_array( $value ) ) {
                        return $value[ 'rendered' ];
                    } else {
                        return $value;
                    }

                }

            }

        }

    }

    // VALIDATE ATTRIBUTE'S CONTENT
    private function swp_validate_atts( $atts, $default ) {

        if( $atts && $atts != $default ) {
            return true;
        }

    }

    // CONSTRUCT
    public function __construct() {

        if( !is_admin() ){
            add_shortcode( 'swp_pull_contents', array( $this, 'swp_pull_contents_func' ) );
        }

    }

}

$swp_rest = new SWPRestAPIFunction();