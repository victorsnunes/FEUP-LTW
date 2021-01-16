<?php

	/*
	 * $dogs -> array of dog ids
	 * @return associative array that maps id to like count and question count
	 */
	function get_dogs_socials($dogs){

		$ids = array();
		foreach($dogs as $dog){
			array_push($ids, $dog['id']);
		}


		$dbc = Database::instance()->db();

		$qry_str = 'SELECT dogs.id as dog_id, COUNT(DISTINCT favorites.id) as num_favorites, COUNT(DISTINCT comments.id) as num_comments
			FROM dogs 
			LEFT JOIN favorites ON dogs.id=favorites.dog_id
			LEFT JOIN comments ON dogs.id=comments.dog_id
			WHERE dogs.id IN (';
		if(count($ids) == 0){
			$qry_str .= ')';
		}
		else{
			$qry_str .= str_repeat('?,', count($ids) - 1) . '?)';
		}

		$qry_str .= 'GROUP BY dogs.id';
		$stmt = $dbc->prepare($qry_str);
		if(count($ids) == 0){
			$stmt->execute();
		}
		else{
			$stmt->execute($ids);
		}

		$pre_res = $stmt->fetchAll();


		$res = array();
		foreach($pre_res as $index => $elem){
			$res[$elem['dog_id']] = array('num_favorites'=>$elem['num_favorites'], 'num_comments'=>$elem['num_comments']);
		}
		return $res;
	}


	/*
	 * Draws the $entry dog social bar
	 */
	function draw_social_count_bar($entry){
?>
                  <div class="photo-stats">
		  <i class="fa fa-heart pink" aria-hidden="true"></i> <?= $entry['num_favorites'] ?>
		  <i class="fa fa-question-circle blue" aria-hidden="true"></i> <?= $entry['num_comments'] ?>
                  </div>
<?php
	}


	/*
	 * Draws the $entry with given $dog_socials and $alt text
	 */
	function draw_pet_card($entry, $dog_socials, $alt){
?>

            <div class="posts-item">
              <div class="posts-container">
                <div class="posts-inside-container">
                  <img src="<?= $entry['listing_picture']?>" class="posts-image" alt="pet<?= $alt ?>">
                  <div class="fav-button">
		<?php if(isset($_SESSION['id'])){
		?>
		  <button id="fav-<?= $entry['id'] ?>" class="button-heart" onclick="fill(this)">
			  <i class="fa <?= (is_null($entry['favorite_id']) ? 'fa-heart-o' : 'fa-heart')?> pink big" aria-hidden="true"></i>
                    </button>
		<?php } ?>
                  </div>
		<?php
			draw_social_count_bar($dog_socials[$entry['id']]);
		?>
                </div>
                <a href="item.php?id=<?= $entry['id'] ?>">
                  <p><?= $entry['listing_name']?></p>
                </a>
              </div>
            </div>

<?php
	}

?>
