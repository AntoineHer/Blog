<?php
class YcfAjax {

	public function __construct() {

		$this->init();
	}

	public function init() {

		add_action('wp_ajax_delete_contact_form', array($this, 'ycfDeleteContactForm'));
		add_action('wp_ajax_shape-form-element', array($this, 'YcfShapeElementsList'));
		add_action('wp_ajax_change-element-data', array($this, 'ycfChangeElementData'));
		add_action('wp_ajax_remove_element_from_list', array($this, 'YcfElementRemoveFromList'));
		add_action('wp_ajax_element_option_data', array($this, 'elementOptionData'));
		add_action('wp_ajax_delete_sub_option', array($this, 'deleteOptionData'));
		add_action('wp_ajax_add_sub_option-option', array($this, 'addSubOptionOption'));
	}

	public function ycfDeleteContactForm() {

		$postData = $_POST;
		if(!isset($postData)) {
			return false;
		}

		$formId = (int)$postData['formId'];

		if($formId == 0) {
			return false;
		}

		$formDataObj = new YcfContactFormData();
		$formDataObj->deleteFormById($formId);
		return 0;
	}

	public function ycfChangeElementData() {

		check_ajax_referer('ycfAjaxNonce', 'ajaxNonce');

		$elementData = $_POST['editElementData'];
		$formId = $elementData['formCurrentId'];
		$changedElementId = $elementData['changedElementId'];
		$changedValue = $elementData['changedElementValue'];
		$changedKey = $elementData['changedElementKey'];

		if($formId == 0) {
			$formListData = get_option('YcfFormDraft');
		}
		else {
			$formListData = YcfContactFormData::getFormListById($formId);
		}

		if(is_array($formListData) && !empty($formListData)) {
			foreach($formListData as $key => $currentListFieldData) {
				if($currentListFieldData['id'] == $changedElementId) {
					$formListData[$key][$changedKey] = $changedValue;
				}
			}
		}

		update_option('YcfFormDraft', $formListData);
	}

	public function YcfElementRemoveFromList() {

		check_ajax_referer('ycfAjaxNonce', 'ajaxNonce');

		$elementData = $_POST['removeElementData'];
		$elementId = $elementData['id'];
		$draftElements = get_option('YcfFormDraft');

		foreach ($draftElements as $key => $draftElement) {
			if($elementId == $draftElement['id']) {
				unset($draftElements[$key]);
			}
		}

		update_option('YcfFormDraft', $draftElements);
		echo '1';
		die();
	}

	public function addElementsToList($formElement, $contactFormId) {

		if($contactFormId == 0) {
			$formListData = get_option('YcfFormDraft');
		}
		else {
			$formListData = YcfContactFormData::getFormListById($contactFormId);
		}

		$formSize = sizeof($formListData);

//		if(!empty($formElement['options'])) {
//			$formElement['options'] = stripslashes($formElement['options']);
//		}

		array_splice($formListData, $formSize, 0, array($formElement));

		update_option('YcfFormDraft', $formListData);
	}

	public function YcfShapeElementsList() {

		check_ajax_referer('ycfAjaxNonce', 'ajaxNonce');
		$dataArray = get_option('YcfFormElements');
		$formElement = $_POST['formElements'];
		$contactFormId = (int)$_POST['contactFormId'];

		if($_POST['modification'] == 'add-element') {
			$this->addElementsToList($formElement, $contactFormId);
		}

		$currentElement = array();
		$formElementId = $formElement['id'];

		if(!get_option('YcfFormElements')) {
			$dataArray = array();
		}

		$currentElement['type'] = $formElement['type'];
		$currentElement['label'] = $formElement['label'];
		$currentElement['name'] = $formElement['name'];
		$currentElement['options'] = $formElement['options'];
		$currentElement['id'] = $formElementId;
		$args['oderId'] = $formElement['orderNumber'];

		array_push($dataArray, $currentElement);
		$element = YcfFunctions::createAdminViewHtml($formElement, $args);
		echo $element;
		die();

	}

	public function addHiddenAccordionDiv($formElement) {
		$elementId = $formElement['id'];
		ob_start();
		?>
		<div class="ycf-element-options-wrapper ycf-hide-element">
			<div class="ycf-sub-option-wrapper">
				<span class="element-option-sub-label">Label</span>
				<input type="text" class="element-label"  value="<?php echo $formElement['label'];?>" data-id="<?php echo $elementId;?>">
			</div>
			<div class="ycf-sub-option-wrapper">
				<span class="element-option-sub-label">Name</span>
				<input type="text" class="element-name" value="<?php echo $formElement['name']; ?>">
			</div>
		</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function deleteOptionData() {

		check_ajax_referer('ycfAjaxNonce', 'ajaxNonce');
		
		$formId  = $_POST['contactFormId'];
		$elementId = $_POST['elementId'];
		$elementType = $_POST['elementType'];
		$elementOrderId = $_POST['elementOrderId'];
		$elementName = $_POST['elementName'];
		$elementValue = $_POST['elementValue'];
		$modificationType = $_POST['modificationType'];

		$elementOptions = $this->getElementOptionsById($formId, $elementId);

		$fieldOptions =  json_decode($elementOptions['fieldsOptions'], true);
		$fieldsOrder =  json_decode($elementOptions['fieldsOrder'], true);
		$modifiedOptions = $fieldOptions;

		foreach($fieldOptions as $key => $field) {
			if($field['orderId'] == $elementOrderId) {
				unset($modifiedOptions[$key]);

				if(($fieldsOrderKey = array_search($elementOrderId,$fieldsOrder)) !== false) {
					unset($fieldsOrder[$fieldsOrderKey]);
				}
			}
		}

		$fieldOptions = json_encode($modifiedOptions);
		$fieldsOrder = json_encode($fieldsOrder);
		$elementOptions['fieldsOptions'] = addslashes($fieldOptions);
		$elementOptions['fieldsOrder'] = addslashes($fieldsOrder);

		$elementOptions = json_encode($elementOptions);
		$this->changeElementOptions($formId, $elementId, $elementOptions);
		echo "";
		wp_die();
	}
	public function elementOptionData() {

		check_ajax_referer('ycfAjaxNonce', 'ajaxNonce');

		$formId  = $_POST['contactFormId'];
		$elementId = $_POST['elementId'];
		$elementType = $_POST['elementType'];
		$elementOrderId = $_POST['elementOrderId'];
		$elementName = $_POST['elementName'];
		$elementValue = $_POST['elementValue'];
		$modificationType = $_POST['modificationType'];

		$elementOptions = $this->getElementOptionsById($formId, $elementId);

		$fieldOptions =  json_decode($elementOptions['fieldsOptions'], true);

		if($modificationType == 'change') {
			foreach($fieldOptions as $key => $field) {
				if($field['orderId'] == $elementOrderId) {
					$fieldOptions[$key][$elementName] = $elementValue;
				}
			}

			$fieldOptions = json_encode($fieldOptions);
			$elementOptions['fieldsOptions'] = addslashes($fieldOptions);
		}
		$elementOptions = json_encode($elementOptions);
		$this->changeElementOptions($formId, $elementId, $elementOptions);
	}

	public function addSubOptionOption()
	{
		check_ajax_referer('ycfAjaxNonce', 'ajaxNonce');
		$formId  = (int)$_POST['contactFormId'];
		$elementId = (int)$_POST['elementId'];
		$elementType = sanitize_text_field($_POST['elementType']);
		$elementOrderId = (int)$_POST['elementOrderId'];
		$newSubOptionName = sanitize_text_field($_POST['newSubOptionName']);
		$newSubOptionLabel = sanitize_text_field($_POST['newSubOptionLabel']);

		$elementOptions = $this->getElementOptionsById($formId, $elementId);
		$fieldOptions =  json_decode($elementOptions['fieldsOptions'], true);
		$fieldsOrder =  json_decode($elementOptions['fieldsOrder'], true);

		$newSubOption = array(
			'label' => 	$newSubOptionLabel,
			'value' => $newSubOptionName,
			'orderId' => $elementOrderId,
			'options' => ''
		);
		$fieldOptions[] = $newSubOption;
		$fieldsOrder[] = $elementOrderId;
		$fieldOptions = json_encode($fieldOptions);
		$fieldsOrder = json_encode($fieldsOrder);
		$elementOptions['fieldsOptions'] = addslashes($fieldOptions);
		$elementOptions['fieldsOrder'] = addslashes($fieldsOrder);
		$elementOptions = json_encode($elementOptions);
		$this->changeElementOptions($formId, $elementId, $elementOptions);

		echo YcfFunctions::subOptionsGroupOptions($elementOrderId, $elementId, $newSubOptionName, $newSubOptionLabel);
		die();
	}

	public function getElementOptionsById($formId, $elementId) {

		$formListData = get_option("YcfFormDraft");

		$optionsData = array();

		if(empty($formListData)) {
			return $optionsData;
		}

		foreach ($formListData as $key => $draftElement) {
			if($elementId == $draftElement['id']) {
				$optionData = $formListData[$key];
			}
		}

		if(empty($optionData['options'])) {
			return $optionsData;
		}

		$options = json_decode(stripslashes($optionData['options']), true);

		return $options;
	}

	public function changeElementOptions($formId, $elementId, $options) {

		$formListData = get_option('YcfFormDraft');

		foreach ($formListData as $key => $draftElement) {
			if($elementId == $draftElement['id']) {
				$formListData[$key]['options'] = $options;
			}
		}

		update_option('YcfFormDraft', $formListData);
	}
}

$ajaxObj = new YcfAjax();