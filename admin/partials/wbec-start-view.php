

<div class="wrap wbec-wrap">
    <h2>Favoritar Posts</h2>
    <h3>Plugin para favoritar posts</h3>
    <p>Clique na estrela para favoritar ou desfavoritar um post</p>
    <div id="painel">
    	<table class="widefat fixed striped margin-top-bottom15">
    		<thead>
				<tr>
					<th width="40">Fav.</th>
					<th>Título</th>
					<th>Data</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data as $post): ?>
				<tr>
					<td>
						<a class="wbec-fav-btn" href="#" data-id="<?php echo esc_attr($post['id']); ?>">
						<span class="dashicons <?php echo in_array($post['id'], $favs) ? 'dashicons-star-filled' : 'dashicons-star-empty' ?>"></span>
						</a>
					</td>
					<td><?php echo esc_attr($post['title']['rendered']); ?></td>
					<td><?php echo date("d/m/Y" , strtotime($post['date'])); ?></td>
					<td><?php echo esc_attr($post['status']); ?></td>
				</tr>
				<?php endforeach ?>
			</tbody>
    	</table>
    </div>
</div>