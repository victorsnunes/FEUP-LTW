<?php


	class Paginate {
		private $page;
		private $elements_per_page;
		private $popped = false;

		/*
		 * $page -> current page
		 * $elements_per_page -> number of elements per page
		 */
		function __construct($page, $elements_per_page){
			$this->page = $page;
			$this->elements_per_page = $elements_per_page;
		}

		/*
		 * Generates the paginate query with one extra element
		 * to know whether there's one extra page
		 */
		function paginate_query($append){
			$start_element = $this->page * $this->elements_per_page;
			$end_element = $start_element + $this->elements_per_page + 1;
			return $append . ' LIMIT ' . $start_element. ' ,  ' . $end_element;
		}

		/*
		 * If the number of retrieved elements is the number of elements
		 * equal to the page size then there's no more elements in the next
		 * page
		 */
		function paginate_results($stmt){

			$res = $stmt->fetchAll();
			if(count($res) > $this->elements_per_page){
				array_pop($res);
				$this->popped = true;
			}

			return $res;
		}

		function generate_pagination_bottom(){
	?>
			<div id="pagination">


				<?php
				if($this->page != 0){
				?>
					<a href="#back">&lt;</a>
				<?php } ?>
				<?= $this->page ?>

				
				<?php
				if($this->popped){
				?>
					<a href="#next">&gt;</a>
				<?php } ?>
				

			</div>
			
			<script>

				function get_clean_url(changer){
					let urlParams = new URLSearchParams(window.location.search);
					let cleanUrl = window.location.toString().replace(window.location.search, "");


					let newObj = {};
					for(var key of urlParams.keys()){
						if(key == 'page'){
							newObj[key] = changer(urlParams.get(key));
						}
						else{
							newObj[key] = urlParams.get(key);
						}
					}

					if(!('page' in newObj))
						newObj['page'] = 1;

					return cleanUrl + '?' + new URLSearchParams(newObj);
				}

				
				function go_back(event){
					event.preventDefault();
					window.location.replace(get_clean_url((el) => {return el - 1;}));
				}

				function go_next(event){
					event.preventDefault();
					window.location.replace(get_clean_url((el) => {return parseInt(el) + 1;}));
				}

				let nodes = [...document.querySelectorAll('#pagination a')];
				nodes.map((node) => {
					switch(node.href.split('#')[1]){
					case 'back':
						node.onclick = go_back;
						break;

					case 'next':
						node.onclick = go_next;
						break;
					}
				});
				

			</script>

	<?php
		}
	}


?>
