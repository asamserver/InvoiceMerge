<?php

namespace WHMCS\Module\Addon\InvoicePaid\Response;

if (!defined('WHMCS')) {
    die('This file cannot be access directly!');
}

class Response
{
    /**
     * set message in session
     *
     * @param bool   $status
     * @param string $message
     *
     * @return $this
     */
    public function withMessage(bool $status = true, string $message = 'your change successfully stored'): Response
    {
        $_SESSION['InvoicePaid_message'] = compact('status', 'message');

        return $this;
    }

    /**
     * set errors in session
     *
     * @param array $errors
     *
     * @return $this
     */
    public function withErrors(array $errors): Response
    {
        $_SESSION['InvoicePaid_validate_errors'] = $errors;

        return $this;
    }

    /**
     * set old inputs in session
     *
     * @param array $inputs
     *
     * @return $this
     */
    public function withInputs(array $inputs): Response
    {
        $_SESSION['InvoicePaid_old_inputs'] = $inputs;

        return $this;
    }

    /**
     * redirect to url
     *
     * @param string $url
     *
     * @return void
     */
    public function redirect(string $url)
    {
        if (headers_sent()) {
            echo '<script>window.location.href="' . $url . '";</script>';
        } else {
            header("Location: $url");
            die();
        }
    }

    /**
     * redirect to previous url
     *
     * @return void
     */
    public function redirectBack()
    {
        $url = str_replace('amp;', '', $_SESSION['InvoicePaid_prev_url']);

        $this->redirect($url);
    }
}
