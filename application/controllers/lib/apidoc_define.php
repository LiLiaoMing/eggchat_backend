<?php

/**
 * @apiDefine Authentication
 * @apiHeader {String} authentication <code>mandatory</code> Authentication token for user
 */


/**
 * @apiDefine Response
 * @apiSuccess {String} status 		Status of the API call.
 * @apiSuccess {String} message 	Description of the API call status.
 */

/**
 * @apiDefine SearchOption
 * @apiParam {Number} limit 	<code>optional</code>Limit.
 * @apiParam {Number} offset 	<code>optional</code>Offset.
 */ 


/**
 * @apiDefine TagsResponse
 * @apiSuccess {Object[]}   data                Result of the API call.
 * @apiSuccess {Number}     .id                 ID of the Tag.
 * @apiSuccess {Number}     .category_id        Category_ID of the Tag.
 * @apiSuccess {String}     .category_name      Category_Name of the Tag.
 * @apiSuccess {String}     .name               Name of the Tag.
 * @apiSuccess {String}     .desc               Description of the Tag.
 */

/**
 * @apiDefine SectionsResponse
 * @apiSuccess {Object[]}   data                Result of the API call.
 * @apiSuccess {Number}     .id                 ID of the Section.
 * @apiSuccess {Number}     .category_id        Category_ID of the Section.
 * @apiSuccess {String}     .category_name      Category_Name of the Section.
 * @apiSuccess {String}     .name               Name of the Section.
 * @apiSuccess {String}     .desc               Description of the Section.
 */

/**
 * @apiDefine TotalNumberResponse
 * @apiSuccess {Number}   data                Total number.
 */

