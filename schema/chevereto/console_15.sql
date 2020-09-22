# Optimized joined query example
EXPLAIN SELECT uc.user_id, uc.count_like, uc.count_image, u.name, u.username FROM user_count uc
LEFT JOIN user u ON u.id = uc.user_id
ORDER BY uc.count_like DESC;

# Note that index must be for all count_*, missing column count_album causes full table scan:
EXPLAIN SELECT uc.user_id, uc.count_like, uc.count_image, uc.count_album, u.name, u.username FROM user_count uc
LEFT JOIN user u ON u.id = uc.user_id
ORDER BY uc.count_like DESC;