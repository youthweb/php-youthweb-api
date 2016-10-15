<?php

namespace Youthweb\Api;

/**
 * Json Object class
 */
final class JsonObject implements \JsonSerializable
{
	/**
	 * serialize the object to a JSON string
	 *
	 * @retrun the JSON string
	 **/
	public function __toString()
	{
		return json_encode($this);
	}

	/**
	 * JsonSerializable implementation
	 *
	 * @return string
	 **/
	public function JsonSerialize()
	{
		return $this;
	}
}
