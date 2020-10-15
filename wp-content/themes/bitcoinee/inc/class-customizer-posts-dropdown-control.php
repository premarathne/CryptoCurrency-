<?php
if ( ! class_exists( 'WP_Customize_Control' ) )
    return NULL;

class Bitcoinee_Category_Control extends WP_Customize_Control {
    public $type = 'select';
    public function render_content() {
    
    $bitcoinee_categories = get_categories(); ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <select <?php $this->link(); ?>>
            <?php
                foreach ( $bitcoinee_categories as $category) :?>
                        <option value="<?php echo $category->cat_ID; ?>" ><?php echo $category->cat_name; ?></option>
                    <?php endforeach; ?>
            </select>
        </label>
    <?php 
    }
}