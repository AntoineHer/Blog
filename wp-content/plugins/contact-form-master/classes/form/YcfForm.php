<?php
class YcfForm {

	private $formId;
	private $formElementsData;

	public function __call($name, $args) {

		$methodPrefix = substr($name, 0, 3);
		$methodProperty = lcfirst(substr($name,3));

		if ($methodPrefix=='get') {
			return $this->$methodProperty;
		}
		else if ($methodPrefix=='set') {
			$this->$methodProperty = $args[0];
		}
	}

	public function createSelectBox($elementData, $index, $args) {

		$name = $elementData['name'];

		$data = static::getOrderedDataFromOptions($elementData);
		if(empty($data)) {
			return '';
		}
		$element = '<div class="ycf-form-element" ycf-data-order="'.$index.'">
						<div class="ycf-form-label-wrapper">
							<span class="ycf-form-label">'.$elementData['label'].'</span>
						</div>
						<div class="ycf-form-element-wrapper">
							'. YcfFunctions::createSelectBox($data, '', array('name'=> $name)) .'
						</div>
					</div>';
		return $element;
	}

	public function createSimpleInput($elementData, $index, $args) {

		$element = '<div class="ycf-form-element" ycf-data-order="'.$index.'">
						<div class="ycf-form-label-wrapper">
							<span class="ycf-form-label">'.$elementData['label'].'</span>
						</div>
						<div class="ycf-form-element-wrapper">
							<input type="'.$elementData['type'].'" name="'.$elementData['name'].'" value="'.$elementData['value'].'">
						</div>
					</div>';

		return $element;
	}

	public function createTextareaElement($elementData, $index, $args) {

		$element = '<div class="ycf-form-element"  ycf-data-order="'.$index.'">
						<div class="ycf-form-label-wrapper">
							<span class="ycf-form-label">'.$elementData['label'].'</span>
						</div>
						<div class="ycf-form-element-wrapper">
							<textarea name="'.$elementData['name'].'"></textarea>
						</div>
					</div>';

		return $element;
	}

	public function createSubmitButton($elementData, $index, $args) {

		$element = '<div class="ycf-submit">
					<input type="'.$elementData['type'].'" id="ycf-submit-button" value="'.$elementData['value'].'">
					<img src="'.YCF_IMG_URL.'loading.gif" class="ycf-hide ycf-spinner">
				</div>';

		return $element;
	}

	public function defaultFormObjectData() {

		$formData = array(
			0 => array('id'=> 5512,'type'=>'text','name'=>'ycf-name','label'=>'Name','value'=>'','options' => '', ),
			1 => array('id'=> 1248,'type'=>'email','name'=>'ycf-email','label'=>'Email','value'=>'','options' => ''),
			2 => array('id'=> 9517,'type'=>'textarea','name'=>'ycf-message','label'=>'Message','value'=>'','options' => ''),
			3 => array('id'=> 'ycf-submit-wrapper','type'=>'submit','name'=>'ycf-submit','label'=>'Submit','value'=>'Submit','options' => '')
		);

		return $formData;
	}
}
