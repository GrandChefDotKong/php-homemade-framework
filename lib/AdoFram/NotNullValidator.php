<?php
namespace AdoFram;

class NotNullValidator extends Validator {
	
	public function isValid($value) {
		
		return $value != '';
	}
}


