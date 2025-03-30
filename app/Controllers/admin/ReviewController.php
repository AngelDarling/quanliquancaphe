<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Review;
use App\Models\Feedback;

class ReviewController extends Controller
{
    public function index()
    {
        $pdo = PDO();
        $reviews = (new Review($pdo))->all();

        $data = [
            'reviews' => $reviews,
            'title' => 'Manage Reviews - Quán cà phê',
            'active' => 'reviews'
        ];

        $this->sendPage('admin/reviews/index', $data);
    }

    public function feedback($id)
    {
        $pdo = PDO();
        $review = (new Review($pdo))->where('id', $id);

        if (!$review) {
            $_SESSION['errors']['general'] = 'Đánh giá không tồn tại.';
            redirect('/admin/reviews');
        }

        $data = [
            'review' => $review,
            'title' => 'Add Feedback - Quán cà phê',
            'active' => 'reviews'
        ];

        $this->sendPage('admin/reviews/feedback', $data);
    }

    public function storeFeedback($id)
    {
        $pdo = PDO();
        $review = (new Review($pdo))->where('id', $id);

        if (!$review) {
            $_SESSION['errors']['general'] = 'Đánh giá không tồn tại.';
            redirect('/admin/reviews');
        }

        $feedback = new Feedback($pdo);
        $data = [
            'review_id' => $id,
            'admin_id' => ADMIN_GUARD()->admin()->id, // Giả định admin đã đăng nhập
            'content' => $_POST['content'] ?? '',
            'feedback_date' => date('Y-m-d H:i:s')
        ];

        $errors = $feedback->validate($data);
        if (empty($errors)) {
            $feedback->fill($data)->save();
            $review->feedback = $data['content']; // Cập nhật feedback trong review
            $review->save();
            $_SESSION['messages']['success'] = 'Thêm phản hồi thành công!';
            redirect('/admin/reviews');
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $data;
        redirect('/admin/reviews/feedback/' . $id);
    }
}
