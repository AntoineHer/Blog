<?php
require_once(YCF_CLASSES_FORM."YcfForm.php");
class YcfBuilder extends YcfForm {

	public function createFormAdminElement() {

		$formElements = $this->getFormElementsData();

		$content = '';
		$oderKeys = implode(',',array_keys($formElements));

		foreach($formElements as $key => $formElement) {
			$args['oderId'] = $key;
			$content .= YcfFunctions::createAdminViewHtml($formElement, $args);
		}
		$content .= '<input type="hidden" name="contact-fields-order" class="form-element-ordering" value="'.$oderKeys.'">';
		
		return $content;
	}

	public function render() {

		$formId = $this->getFormId();
		$formData = $this->getFormElementsData();
		$args = array();

		$contactForm = '<form id="ycf-contact-form" class="ycf-contact-form ycf-form-'.$formId.'" action="admin-post.php" method="post">';

		foreach ($formData as $index => $formInfo) {

			switch($formInfo['type']) {

				case 'text':
				case 'email':
				case 'number':
				case 'url':
					$contactForm .= $this->createSimpleInput($formInfo, $index, $args);
					break;
				case 'textarea':
					$contactForm .= $this->createTextareaElement($formInfo, $index, $args);
					break;
				case 'select':
					$contactForm .= $this->createSelectBox($formInfo, $index, $args);
					break;
				case 'submit':
					$contactForm .= $this->createSubmitButton($formInfo, $index, $args);
					break;
			}
		}
		$contactForm .= '</form>';

		return $contactForm;
		return $formId;
	}

	public function getOrderedDataFromOptions($formInfo) {

		$data = array();

		if(empty($formInfo['options'])) {
			return $data;
		}
		$options = stripslashes($formInfo['options']);

		$options = json_decode($options, true);

		if(empty($options)) {
			return $data;
		}

		$fieldOptions = $options['fieldsOptions'];
		$fieldOptions = json_decode($fieldOptions, true);

		$fieldsOrder = $options['fieldsOrder'];
		$fieldsOrder = json_decode($fieldsOrder, true);

		if(empty($fieldOptions) || empty($fieldsOrder)) {
			return $data;
		}

		foreach($fieldsOrder as $orderId) {
			foreach($fieldOptions as $field) {

				if($field['orderId'] == $orderId) {
					$value= $field['value'];
					$data[$value] = $field['label'];
				}
			}
		}

		return $data;
	}
}