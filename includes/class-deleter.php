<?php

if (!defined('ABSPATH')) {
    exit;
}

class AutoDeletePostDeleter {
    private $logger;
    private $settings;
    
    public function __construct($logger, $settings) {
        $this->logger = $logger;
        $this->settings = $settings;
    }
    
    public function executeDeleteProcess() {
        if (!$this->settings->isEnabled()) {
            $this->logger->logInfo('Deletion process is disabled. Skipping execution.');
            return;
        }
        
        $this->logger->logInfo('Starting deletion process execution');
        
        $totalDeleted = 0;
        
        if ($this->settings->isPostDeletionEnabled()) {
            $totalDeleted += $this->deletePosts();
        }
        
        if ($this->settings->isCommentDeletionEnabled()) {
            $totalDeleted += $this->deleteComments();
        }
        
        if ($this->settings->isCategoryDeletionEnabled()) {
            $totalDeleted += $this->deleteCategories();
        }
        
        if ($this->settings->isTagDeletionEnabled()) {
            $totalDeleted += $this->deleteTags();
        }
        
        $this->logger->logInfo("Deletion process completed. Total items deleted: {$totalDeleted}");
    }
    
    private function deletePosts() {
        $limit = $this->settings->getPostLimit();
        $deleteAttachments = $this->settings->shouldDeleteAttachments();
        
        $this->logger->logInfo("Starting post deletion. Limit: {$limit}, Delete attachments: " . ($deleteAttachments ? 'Yes' : 'No'));
        
        // Build query arguments
        $args = array(
            'numberposts' => $limit,
            'post_status' => 'any',
            'post_type' => 'any'
        );
        
        // Apply date filter if enabled
        if ($this->settings->isDateFilterEnabled()) {
            $dateStart = $this->settings->getDateStart();
            $dateEnd = $this->settings->getDateEnd();
            $dateMode = $this->settings->getDateFilterMode();
            
            if (!empty($dateStart) && !empty($dateEnd)) {
                if ($dateMode === 'include') {
                    $args['date_query'] = array(
                        array(
                            'after' => $dateStart,
                            'before' => $dateEnd,
                            'inclusive' => true
                        )
                    );
                } else { // exclude
                    $args['date_query'] = array(
                        'relation' => 'OR',
                        array(
                            'before' => $dateStart,
                            'inclusive' => false
                        ),
                        array(
                            'after' => $dateEnd,
                            'inclusive' => false
                        )
                    );
                }
                $this->logger->logInfo("Date filter applied: {$dateMode} posts from {$dateStart} to {$dateEnd}");
            }
        }
        
        // Apply user filter if enabled
        if ($this->settings->isUserFilterEnabled()) {
            $selectedUsers = $this->settings->getSelectedUsers();
            $userMode = $this->settings->getUserFilterMode();
            
            if (!empty($selectedUsers)) {
                if ($userMode === 'include') {
                    $args['author__in'] = $selectedUsers;
                } else { // exclude
                    $args['author__not_in'] = $selectedUsers;
                }
                $userList = implode(', ', $selectedUsers);
                $this->logger->logInfo("User filter applied: {$userMode} users [{$userList}]");
            }
        }
        
        $posts = get_posts($args);
        
        $deletedCount = 0;
        
        foreach ($posts as $post) {
            try {
                if ($deleteAttachments) {
                    $this->deletePostAttachments($post->ID);
                }
                
                $result = wp_delete_post($post->ID, true);
                
                if ($result !== false) {
                    $deletedCount++;
                    $this->logger->logSuccess("Post ID {$post->ID} ('{$post->post_title}') deleted successfully");
                } else {
                    $this->logger->logError("Failed to delete Post ID {$post->ID}");
                }
            } catch (Exception $e) {
                $this->logger->logError("Exception while deleting Post ID {$post->ID}: " . $e->getMessage());
            }
        }
        
        $this->settings->updateStatistics('posts', $deletedCount);
        $this->logger->logInfo("Post deletion completed. Deleted {$deletedCount} of " . count($posts) . " posts");
        
        return $deletedCount;
    }
    
    private function deletePostAttachments($postId) {
        $attachments = get_attached_media('', $postId);
        
        foreach ($attachments as $attachment) {
            $result = wp_delete_attachment($attachment->ID, true);
            if ($result) {
                $this->logger->logInfo("Attachment ID {$attachment->ID} deleted for Post ID {$postId}");
            } else {
                $this->logger->logWarning("Failed to delete Attachment ID {$attachment->ID} for Post ID {$postId}");
            }
        }
    }
    
    private function deleteComments() {
        $limit = $this->settings->getCommentLimit();
        
        $this->logger->logInfo("Starting comment deletion. Limit: {$limit}");
        
        $comments = get_comments(array(
            'number' => $limit,
            'status' => 'any'
        ));
        
        $deletedCount = 0;
        
        foreach ($comments as $comment) {
            try {
                $result = wp_delete_comment($comment->comment_ID, true);
                
                if ($result) {
                    $deletedCount++;
                    $this->logger->logSuccess("Comment ID {$comment->comment_ID} deleted successfully");
                } else {
                    $this->logger->logError("Failed to delete Comment ID {$comment->comment_ID}");
                }
            } catch (Exception $e) {
                $this->logger->logError("Exception while deleting Comment ID {$comment->comment_ID}: " . $e->getMessage());
            }
        }
        
        $this->settings->updateStatistics('comments', $deletedCount);
        $this->logger->logInfo("Comment deletion completed. Deleted {$deletedCount} of " . count($comments) . " comments");
        
        return $deletedCount;
    }
    
    private function deleteCategories() {
        $limit = $this->settings->getCategoryLimit();
        $deleteEmptyOnly = $this->settings->shouldDeleteEmptyCategoriesOnly();
        
        $this->logger->logInfo("Starting category deletion. Limit: {$limit}, Delete empty only: " . ($deleteEmptyOnly ? 'Yes' : 'No'));
        
        if ($deleteEmptyOnly) {
            $terms = get_terms(array(
                'taxonomy' => 'category',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'count',
                        'value' => 0,
                        'compare' => '='
                    )
                )
            ));
            
            $emptyTerms = array();
            foreach ($terms as $term) {
                if ($term->count == 0) {
                    $emptyTerms[] = $term;
                    if (count($emptyTerms) >= $limit) {
                        break;
                    }
                }
            }
            $terms = $emptyTerms;
        } else {
            $terms = get_terms(array(
                'taxonomy' => 'category',
                'number' => $limit,
                'hide_empty' => false
            ));
        }
        
        $deletedCount = 0;
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                try {
                    $result = wp_delete_term($term->term_id, 'category');
                    
                    if (!is_wp_error($result) && $result !== false) {
                        $deletedCount++;
                        $this->logger->logSuccess("Category ID {$term->term_id} ('{$term->name}') deleted successfully");
                    } else {
                        $errorMessage = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
                        $this->logger->logError("Failed to delete Category ID {$term->term_id}: {$errorMessage}");
                    }
                } catch (Exception $e) {
                    $this->logger->logError("Exception while deleting Category ID {$term->term_id}: " . $e->getMessage());
                }
            }
        } else {
            $this->logger->logError('Error retrieving categories: ' . $terms->get_error_message());
        }
        
        $this->settings->updateStatistics('categories', $deletedCount);
        $this->logger->logInfo("Category deletion completed. Deleted {$deletedCount} of " . count($terms) . " categories");
        
        return $deletedCount;
    }
    
    private function deleteTags() {
        $limit = $this->settings->getTagLimit();
        $deleteEmptyOnly = $this->settings->shouldDeleteEmptyTagsOnly();
        
        $this->logger->logInfo("Starting tag deletion. Limit: {$limit}, Delete empty only: " . ($deleteEmptyOnly ? 'Yes' : 'No'));
        
        if ($deleteEmptyOnly) {
            $tags = get_terms(array(
                'taxonomy' => 'post_tag',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'count',
                        'value' => 0,
                        'compare' => '='
                    )
                )
            ));
            
            $emptyTags = array();
            foreach ($tags as $tag) {
                if ($tag->count == 0) {
                    $emptyTags[] = $tag;
                    if (count($emptyTags) >= $limit) {
                        break;
                    }
                }
            }
            $tags = $emptyTags;
        } else {
            $tags = get_terms(array(
                'taxonomy' => 'post_tag',
                'number' => $limit,
                'hide_empty' => false
            ));
        }
        
        $deletedCount = 0;
        
        if (!is_wp_error($tags)) {
            foreach ($tags as $tag) {
                try {
                    $result = wp_delete_term($tag->term_id, 'post_tag');
                    
                    if (!is_wp_error($result) && $result !== false) {
                        $deletedCount++;
                        $this->logger->logSuccess("Tag ID {$tag->term_id} ('{$tag->name}') deleted successfully");
                    } else {
                        $errorMessage = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
                        $this->logger->logError("Failed to delete Tag ID {$tag->term_id}: {$errorMessage}");
                    }
                } catch (Exception $e) {
                    $this->logger->logError("Exception while deleting Tag ID {$tag->term_id}: " . $e->getMessage());
                }
            }
        } else {
            $this->logger->logError('Error retrieving tags: ' . $tags->get_error_message());
        }
        
        $this->settings->updateStatistics('tags', $deletedCount);
        $this->logger->logInfo("Tag deletion completed. Deleted {$deletedCount} of " . count($tags) . " tags");
        
        return $deletedCount;
    }
}