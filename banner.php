<?php
/**
 * @package  BannerPlugin
 */

/*
Plugin Name: Banner Plugin
Plugin URI: https://github.com/dedevillela/Banner-Plugin
Description: A simple banner plugin.
Version: 1.0.0
Author: Andre Aguiar Villela
Author URI: https://dedevillela.com
License: GPLv2 or later
Text Domain: banner-plugin
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2019 André Aguiar Villela.
*/
defined( 'ABSPATH' ) or die( 'Access denied.' );

class BannerPlugin
{
	public function __construct() {
	    // Inicializa o post_type.
		add_action( 'init', array( $this, 'custom_post_type' ) );
		// Adiciona o meta box do link.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		// Salva os dados do meta box.
        add_action( 'save_post', array( $this, 'save' ) );
        // Adiciona tamanho de imagem do banner.
		add_image_size( 'banner', 960, 300, true );
		// Adiciona o shortcode.
		add_shortcode( 'banner-cta', array( $this, 'banner_shortcode' ) );
	}
	public function activate() {
		// gera o post_type ao ativar o plugin
		$this->custom_post_type();
		// Remove as regras de rewrite ao ativar o plugin e as recria.
		flush_rewrite_rules();
	}
	public function deactivate() {
		// Remove as regras de rewrite ao desativar o plugin e as recria.
		flush_rewrite_rules();
	}
	public function custom_post_type() {
	    // Registra o post_type "banner".
	    $labels = array(
        'name'                  => _x( 'Banners', 'Post type general name', 'banner-plugin' ),
        'singular_name'         => _x( 'Banner', 'Post type singular name', 'banner-plugin' ),
        'menu_name'             => _x( 'Banners', 'Admin Menu text', 'banner-plugin' ),
        'name_admin_bar'        => _x( 'Banner', 'Add New on Toolbar', 'banner-plugin' ),
        'add_new'               => __( 'Adicionar Novo', 'banner-plugin' ),
        'add_new_item'          => __( 'Adicionar Novo Banner', 'banner-plugin' ),
        'new_item'              => __( 'Novo Banner', 'banner-plugin' ),
        'edit_item'             => __( 'Editar Banner', 'banner-plugin' ),
        'view_item'             => __( 'Ver Banner', 'banner-plugin' ),
        'all_items'             => __( 'Todos os Banners', 'banner-plugin' ),
        'search_items'          => __( 'Procurar Banners', 'banner-plugin' ),
        'parent_item_colon'     => __( 'Banner Ascendente:', 'banner-plugin' ),
        'not_found'             => __( 'Banners não encontrados.', 'banner-plugin' ),
        'not_found_in_trash'    => __( 'Banners não encontrados na Lixeira.', 'banner-plugin' ),
        'featured_image'        => _x( 'Imagem do Banner', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'banner-plugin' ),
        'set_featured_image'    => _x( 'Definir imagem do Banner', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'banner-plugin' ),
        'remove_featured_image' => _x( 'Remover imagem do Banner', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'banner-plugin' ),
        'use_featured_image'    => _x( 'Usar como imagem do Banner', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'banner-plugin' ),
        'insert_into_item'      => _x( 'Inserir no Banner', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'banner-plugin' ),
        'uploaded_to_this_item' => _x( 'Enviado para este Banner', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'banner-plugin' ),
        'filter_items_list'     => _x( 'Filtrar lista de Banners', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'banner-plugin' ),
        'items_list_navigation' => _x( 'Navegação na lista de Banners', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'banner-plugin' ),
        'items_list'            => _x( 'Lista de Banner', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'banner-plugin' ),
    );
 
    $args = array(
        'labels'               => $labels,
        'public'               => false,
        'publicly_queryable'   => true,
        'show_ui'              => true,
        'show_in_menu'         => true,
        'query_var'            => true,
        'rewrite'              => array( 'slug' => 'banner' ),
        'capability_type'      => 'post',
        'has_archive'          => false,
        'hierarchical'         => false,
        'menu_position'        => null,
        'register_meta_box_cb' => 'add_banner_metaboxes',
		'menu_icon'            => 'dashicons-feedback',
        'supports'             => array( 'title', 'thumbnail' ),
    );
    
    register_post_type( 'banner', $args );

	}
	
	public function add_meta_box( $post_type ) {
        // Define o post_type.
        $post_type = 'banner';
        // Adiciona o meta box.
        add_meta_box(
            'link_meta_box',
            __( 'Link e Shortcode do Banner', 'banner-plugin' ),
            array( $this, 'banner_meta_box_content' ),
            $post_type,
            'advanced',
            'high'
        );
    }
    
    public function save( $post_id ) {
        /*
         * Devemos verificar se há permissão para salvar,
         * pois save_post pode ser disparado outras vezes.
         */
 
        // Checa se o nonce foi definido.
        if ( ! isset( $_POST['banner_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }
 
        $nonce = $_POST['banner_inner_custom_box_nonce'];
 
        // Verifica se o nonce é valido.
        if ( ! wp_verify_nonce( $nonce, 'banner_inner_custom_box' ) ) {
            return $post_id;
        }
 
        /*
         * Se for um autosave, o form não foi enviado,
         * então nada é feito.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
 
        // Checa as permissões do usuário.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
 
        /* OK, está seguro salvar os dados agora. */
 
        // Sanitariza o input do usuário.
        $banner_data = sanitize_text_field( $_POST['banner_url_field'] );
 
        // Atualiza o campo do meta.
        update_post_meta( $post_id, '_banner_meta_value_key', $banner_data );
    }
 
    /**
     * Renderiza o conteúdo do Meta Box.
     *
     * @param WP_Post $post The post object.
     */
    public function banner_meta_box_content( $post ) {
        // Adiciona um campo nonce para verificação posterior.
        wp_nonce_field( 'banner_inner_custom_box', 'banner_inner_custom_box_nonce' );
 
        // Usa get_post_meta para armazenar um valor existente no banco de dados.
        $value = get_post_meta( $post->ID, '_banner_meta_value_key', true );
 
        // Exibe o form usando o valor atual.
        ?>
        <p class="description">
        <label for="banner_url_field">
            <?php _e( 'Insira a URL de destino', 'banner-plugin' ); ?>
        </label>
        <input type="url" id="banner_url_field" name="banner_url_field" value="<?php echo esc_attr( $value ); ?>" class="widefat" />
        </p>
        <p class="description">
        <label for="banner_shortcode_field">
            <?php _e( 'Shortcode do Banner', 'banner-plugin' ); ?>
        </label>
        <input type="text" readonly="readonly" id="banner_shortcode_field" onfocus="this.select();" name="banner_shortcode_field" value="[banner-cta id='<?php echo get_the_ID( $post_id ); ?>']" class="widefat code" />
        </p>
        <?php
    }
    
    public function banner_shortcode( $atts ) {
        // Cria o shortcode com o atributo "id".
        extract(
            shortcode_atts(
                array(
                    'id' => '',
                ),
                $atts
            )
        );
        // Retorna a saída HTML do shortcode.
        if ( isset( $id ) ) {
            return '<a href="' . get_post_meta( $id, '_banner_meta_value_key', true ) . '" target="_blank">' . get_the_post_thumbnail( $id, 'banner' ) . '</a>';
        }
    }
    
}

if ( class_exists( 'BannerPlugin' ) ) {
    // Verifica se a classe existe e cria uma nova instancia
	$bannerPlugin = new BannerPlugin();
}
// Hook de ativação do plugin
register_activation_hook( __FILE__, array( $bannerPlugin, 'activate' ) );
// Hook de desativação do plugin
register_deactivation_hook( __FILE__, array( $bannerPlugin, 'deactivate' ) );
