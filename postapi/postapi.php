<?php

/*
Plugin Name: Post API
Plugin URI: 
Description: make endpoint post
Version: 1
Author URI: https://automattic.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: postapi
*/

class PostAPI
{
    public function __construct()
    {
        add_action("init", [$this, "registerEndpoint"]);
        add_action( "save_post_post", [$this, "saveCreateByMeta"], 10, 3);
    }

    public function saveCreateByMeta($post_id, $post, $update)
    {
        if(!$update)
        {
            update_post_meta($post_id, "created_by", "bot");
        }
    }


    public function registerEndpoint()
    {
        register_rest_route( "testplugin/v1",  "posts",  array(
            "methods" => "GET",
            "callback" => [$this, "posts"],
        ));
    }

    public function posts()
    {
        $postArgs = [
            "post_type" => "post",
            "post_status" => "publish",
            "numberposts" => 10,
            "meta_query" => [
                [
                    "key" => "created_by",
                    "value" => "bot",
                    "compare" => "="
                ],
            ]
        ];

        $posts = get_posts($postArgs);

        $posts = array_map(function(WP_Post $post){
            return [
                "title" => $post->post_title,
                "author" => $post->post_author,
                "category" => $this->getCategories($post->ID),
                "postContent" => $post->post_content
            ];
        }, $posts);

        return wp_send_json_success(
            [
                "error" => false,
                "message" => "success get posts",
                "data" => $posts,
            ]
        );
    }

    private function getCategories( $postId )
    {
        $terms = wp_get_post_terms($postId, "category");
        if(!is_wp_error($terms))
        {
            $terms = array_map(function(WP_Term $term) {
                return [
                    "title"=> $term->name,
                    "id" => $term->term_id,
                ];
            }, $terms);
        }
        return $terms;
    }
}

new PostAPI;