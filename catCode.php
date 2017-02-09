<?php

if($_GET['pass'] === "uniquePassword"){
	echo 'Access Granted';
}
else{
	die("NO");
}

$db = new mysqli('localhost', 'redacted', 'redacted', 'redacted');
if($db->connect_errno){
	die("Could Not Connect");
}
else{
	echo '<br /> DB Connection Success';
}

?>
<br />

<form method="get">
	Store:
	<input type="text" name="store"></input>
	<br />
	Category Id: 
	<input type="number" name="cat_id"></input>
	<br />
	Pass:
	<input type="text" name="pass"></input>
	<br />
	<input type="submit">Submit</input>
</form>

<br />
<?php

if($_GET['store'] && $_GET['cat_id']){

	$term = $_GET['store'];
	$cat_id = $_GET['cat_id'];


	function addCatByTermInTitle($term, $cat_id){
		global $db;
		$termRelatedPosts = array();
		$publishedPostsSQL = "SELECT * FROM posts WHERE `post_status` = 'publish'"; // 25133
		$result = $db->query($publishedPostsSQL);
		$numPosts = 0;
		while( $row = $result->fetch_assoc() ){
			if( stripos($row['post_title'], $term) != false ){
				$numPosts += 1;
				array_push($termRelatedPosts, $row['ID']);
			}
		}
		echo '<br /> There are ' . $numPosts . ' posts that, in their title contain: ' . $term;
		echo '<br /> Their IDS have been pushed into an array that contains ' . count($termRelatedPosts) . ' IDS ';
		addCatToPosts($termRelatedPosts, $cat_id);
	}


	// match term_id ($cat_id) to term_taxonomy_id in term_taxonomy table
	// Add new row to term_relationships table where object_id equals the post ID
	// and term_taxonomy_id equals the term taxonomy_id of our $cat_slug
	// !!! Warning. This function can create duplicate rows! !!!

	function addCatToPosts($termRelatedPosts, $cat_id){
		global $db;
		$findTermTaxIdSql = "SELECT `term_taxonomy_id` FROM term_taxonomy WHERE `term_id` = '$cat_id'";
		$result = $db->query($findTermTaxIdSql);
		while( $row = $result->fetch_assoc() ){
			$termTaxId = $row['term_taxonomy_id'];
		}
		echo '<br />';
		echo 'Term Tax Id is: ' . $termTaxId;
		  foreach($termRelatedPosts as $post){
			$addCatToPostSql = "INSERT INTO term_relationships (`object_id`, `term_taxonomy_id`, `term_order`) VALUES ('$post', '$termTaxId', '0')";
			$db->query($addCatToPostSql);
		}
		echo '<br />';
		echo 'Inserted ' . count($termRelatedPosts) . ' rows into term_relationships for the category of: ' . $cat_id;
	}

	addCatByTermInTitle($term, $cat_id);

}
else{
	echo '<br />';
	echo 'You need to enter a store and a category ID';
}

?>
