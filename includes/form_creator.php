<?php

  class FormCreator {
    
    private $id = NULL;
    private $ajax = NULL;
    private $action = NULL;
    private $popup = NULL;
    private $error_zone = false;
    private $elements = array();
    private $enctype = NULL;

	/*
	 * $id -> id of the form
	 * $action -> url where it will be posted to
	 * $error_zone -> whether it has a error_zon
	 * $ajax -> whether it is ajax
	 * $popup -> whether it is a popup
	 * $enctype -> enctype of requets
	 */
    function __construct($id, $action, $error_zone = false, $ajax=true, $popup=true, $enctype=NULL) {
	    $this->id = $id;
	    $this->action = $action;
	    $this->error_zone = $error_zone;
	    $this->ajax = $ajax;
	    $this->popup = $popup;
	    $this->enctype = $enctype;
    }


	/*
	 * $name -> name of the input
	 * Rest are html attributes
	 */
    function add_input($name, $label = "Label", $type = "text", $placeholder="", $required = true, $value = NULL, $pattern = NULL){
	    array_push($this->elements, new FormInput($name, $label, $type, $placeholder, $required, $value, $pattern));
    }

	/*
	 * $name -> name of the select
	 * Rest are html attributes
	 */
    function add_select($name, $label, $options, $selected=NULL){
	    array_push($this->elements, new FormSelect($name, $label, $options, $selected));
    }


	/*
	 * Writes the form to html
	 */
    function inline(){
	    $form_class = $this->popup ? 'overlayLogin' : '';
?>

	<div id="<?= $this->id ?>" class="<?= $form_class ?>">
	<form class="overlayLogin-content animate nopad" action="<?= $this->action ?>" method="post" <?= (is_null($this->enctype) ? '' : 'enctype="'.$this->enctype.'"');?>>


      <div class="container top round">
	<?php
		if($this->error_zone) {
	?>
	<div id="<?= $this->id ?>-errors" class="error-div">
        </div>
	<?php } ?>

<?php

	if($this->popup){
?>
        <span onclick="document.getElementById('<?= $this->id ?>').style.display='none'; hideErrorDiv(this)" class="close"
          title="close overlayLogin">&#10006;</span>
<?php } ?>
      </div>

      <div class="container">
	
	<?php
	
	    foreach($this->elements as $index => $entry){
		    echo $entry->to_str(bin2hex($this->id));
	    }



	?>
	<input name="csrf" type="hidden" style="display:none" value="<?= $_SESSION['csrf'] ?>">
        <button type="submit" class="login">Submit</button>
      </div>

<?php

	if($this->popup){
?>
      <div class="container bottom round">
        <button type="button" onclick="document.getElementById('<?= $this->id ?>').style.display='none'"
          class="cancel-button">Back</button>
      </div>
<?php } ?>
    </form>
  </div>

<?php

	    if($this->ajax){
	    $handler_function = 'handler_'.bin2hex($this->id);
?>

	
	<script>
	
	    function <?= $handler_function ?>(event){
			event.preventDefault();


			let form = document.querySelector('#<?= $this->id ?> form');

			let correctDiv = form.children[1];
			let body = {};
			for(let i = 0; i<correctDiv.children.length; i++){

				let child = correctDiv.children[i];

				if(child.name == "")
					continue;

				switch(child.nodeName.toLowerCase()){
					case 'input':
						if(child.type == "checkbox")
							body[child.name] = child.checked;
						else
							body[child.name] = child.value;
						break;
					case 'label':
						break;
					case 'br':
						break;
					case 'select':
						body[child.name] = child.value;
						break;
					default:
						console.log('need to implement ' + child.nodeName);
						break;
				}

			}

			let errorzone = document.getElementById('<?= $this->id ?>-errors');
			errorzone.innerHTML = '';


			fetch('<?= $this->action ?>', {
				method:'POST',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json',
				},
					body: JSON.stringify(body)
			}).then((text) => {
				return text.json();
				
			}).then( (json) => {

				if('errors' in json){
					errorzone.innerHTML = json['errors'];
					return;
				}

				location.reload();

				
			});
		}
	    document.getElementById('<?=$this->id ?>').children[0].addEventListener('submit',  <?= $handler_function ?>);
		



	</script>

<?php
    }



    }

  }


	
  abstract class FormElement {
	  abstract function to_str($input);
  }

  class FormInput extends FormElement{
    
    private $name = NULL;
    private $label = NULL;
    private $type = NULL;
    private $placeholder = NULL;
    private $required = NULL;
    private $pattern = NULL;
    private $value = NULL;

    function __construct($name, $label, $type, $placeholder, $required, $value, $pattern) {
	    $this->name = $name;
	    $this->label = $label;
	    $this->type = $type;
	    $this->placeholder = $placeholder;
	    $this->required = $required;
	    $this->pattern = $pattern;
	    $this->value = $value;
    }



    function to_str($input){
	    $item_id = $input . '-' . $this->name;

		/*
		 * Checkboxes need to be in the reverse order
		 */
	    if($this->type !== "checkbox"){

?>
	<label for="<?= $item_id ?>"><b><?= $this->label ?></b></label>
	<?php } ?>
	<input id="<?= $item_id ?>" type="<?= $this->type ?>" placeholder="<?= $this->placeholder ?>" name="<?= $this->name ?>" 
	<?php echo (!is_null($this->value) ? 'value="'.$this->value.'" ' : ''); ?> <?php echo ($this->required ? 'required' : ''); ?> <?php echo (!is_null($this->pattern) ? 'pattern="'.$this->pattern.'"': '');?> >

	<?php
	
	    if($this->type == "file"){
		    echo '<br><br>';
	    }
	    else if($this->type == "checkbox"){
?>
		<label for="<?= $item_id ?>"><b><?= $this->label ?></b></label>

		<?php
	    }


    }


  }


  class FormSelect extends FormElement{
	

	private $name = NULL;
	private $label = NULL;
	private $options = NULL;
	private $selected = NULL;
	function __construct($name, $label, $options, $selected) {
		$this->name = $name;
		$this->label = $label;
		$this->options = $options;
		$this->selected = $selected;
	}


	function to_str($input){
		$item_id = $input . '-' . $this->name;


?>
		
		<label for="<?=$item_id?>"><?= $this->label ?></label>
		<select id="<?=$item_id?>" name="<?= $this->name ?>" >
<?php

		foreach($this->options as $key => $value){
?>
			<option value="<?= $key ?>"  <?= ( (!is_null($this->selected) && $key == $this->selected)  ? 'selected' : '')?> > <?= $value ?> </option>

<?php
		}
?>
		</select><br><br>
<?php
	}

  }




?>
