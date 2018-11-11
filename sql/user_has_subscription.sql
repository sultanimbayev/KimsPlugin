SELECT 
	count(*) > 0 _exists
FROM `{table_prefix}posts` post
join `{table_prefix}postmeta` meta_user 
	on meta_user.meta_key = '_customer_user'
    and meta_user.post_id = post.id
join `{table_prefix}woocommerce_order_items` _order on _order.order_id = post.id
join `{table_prefix}woocommerce_order_itemmeta` ordermeta on _order.order_item_id = ordermeta.order_item_id
where post.`post_type` = 'shop_subscription'
    and meta_user.meta_value = {user_id}
    and ordermeta.meta_key = '_product_id'
    and ordermeta.meta_value in ({subscr_product_ids})
    and post.post_status in ('wc-completed', 'wc-active')