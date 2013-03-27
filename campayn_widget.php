<?
/**
 * Adds Campayn_Widget widget.
 */
class Campayn_Widget extends WP_Widget {

  /**
   * Register widget with WordPress.
   */
  public function __construct() {
    parent::__construct(
      'campayn_widget', // Base ID
      'Campayn Signup', // Name
      array( 'description' => __( 'Campayn Signup', 'text_domain' ), ) // Args
    );
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  public function widget( $args, $instance ) {
    extract( $args );
    $title = apply_filters( 'widget_title', $instance['title'] );

    print $before_widget;
    if (!empty( $title )) {
     print $before_title . $title . $after_title;
    }
    print campayn_get_form_message($instance['form']);
    print $after_widget;
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['form'] = strip_tags( $new_instance['form'] );
//    $instance['success'] = strip_tags( $new_instance['success'] );

    return $instance;
  }

  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form( $instance ) {
    isset($instance['title']) ? $title = $instance[ 'title' ] : $title = 'Campayn signup';
  //  isset($instance['success']) ? $success = $instance[ 'success' ] : $success = __('Thank you for signing up!');
    
    print '<p>
    <label for="'.$this->get_field_id('title').'">'.__('Title:').'</label> 
    <input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr( $title ).'"/>
    <br/>
    <label for="'.$this->get_field_id('form').'">'.__('Form:').'</label> 
    <select class="widefat" id="'.$this->get_field_id('form').'" name="'.$this->get_field_name('form').'">
      '. campayn_get_forms_as_options($instance['form']) .'
    </select>';
//    <br/>
//    <label for="'.$this->get_field_id('success').'">'.__('Success:').'</label> 
//    <input class="widefat" id="'.$this->get_field_id('success').'" name="'.$this->get_field_name('success').'" type="text" value="'.esc_attr( $success ).'"/>
    print '</p>';
  }

} // class Campayn_Widget

add_action( 'widgets_init', create_function( '', 'register_widget( "campayn_widget" );' ) );

?>
