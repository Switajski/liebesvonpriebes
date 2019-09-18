<?php
/**
 * BackendAccountHelper
 *
 * @package     jtl_backenduser_extension
 * @copyright   2015 JTL-Software-GmbH
 */

/**
 * Class BackendAccountHelper
 */
class BackendAccountHelper
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var BackendAccountHelper
     */
    private static $_instance;

    /**
     * @param Plugin $oPlugin
     * @return BackendAccountHelper
     */
    public static function getInstance(Plugin $oPlugin)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($oPlugin);
        }

        return static::$_instance;
    }

    /**
     * BackendAccountHelper constructor.
     * @param Plugin $oPlugin
     */
    private function __construct(Plugin $oPlugin)
    {
        $this->plugin = $oPlugin;
    }

    /**
     * @param array $tmpFile
     * @param string $attribName
     * @return mixed bool|string
     */
    private function uploadImage(array $tmpFile, $attribName)
    {
        $imgType = array_search($tmpFile['type'][$attribName], [
            IMAGETYPE_JPEG => image_type_to_mime_type(IMAGETYPE_JPEG),
            IMAGETYPE_PNG  => image_type_to_mime_type(IMAGETYPE_PNG),
            IMAGETYPE_BMP  => image_type_to_mime_type(IMAGETYPE_BMP),
            IMAGETYPE_GIF  => image_type_to_mime_type(IMAGETYPE_GIF),
        ], true);

        if ($imgType !== false) {
            $imagePath = PFAD_MEDIA_IMAGE . 'avatare/';
            $imageName = pathinfo($tmpFile['name'][$attribName], PATHINFO_FILENAME) . image_type_to_extension($imgType);

            if (is_dir(PFAD_ROOT . $imagePath) || mkdir(PFAD_ROOT . $imagePath, 0755)) {
                if (move_uploaded_file($tmpFile['tmp_name'][$attribName], PFAD_ROOT . $imagePath . $imageName)) {
                    return '/' . $imagePath . $imageName;
                }
            }
        }

        return false;
    }

    /**
     * @param $imagePath
     */
    private function deleteImage($imagePath)
    {
        if (is_file(PFAD_ROOT . $imagePath)) {
            unlink(PFAD_ROOT . $imagePath);
        }
    }

    /**
     * @param string $paramName
     * @param string|null $defaultValue
     * @return string
     */
    public function getConfigParam($paramName, $defaultValue = null)
    {
        return isset($this->plugin->oPluginEinstellungAssoc_arr[$paramName])
            ? $this->plugin->oPluginEinstellungAssoc_arr[$paramName]
            : $defaultValue;
    }

    /**
     * @param array $contentArr
     * @param string $realm
     * @param string $contentKey
     */
    public function getFrontend($contentArr, $realm, $contentKey)
    {
        if (isset($contentArr) && is_array($contentArr)) {
            foreach ($contentArr as $key => $content) {
                $author = ContentAuthor::getInstance()->getAuthor($realm, $content->$contentKey, true);

                if (isset($author->kAdminlogin) && $author->kAdminlogin > 0) {
                    // Avatar benutzen?
                    if (isset($author->extAttribs['useAvatar']) && $this->getConfigParam('use_avatar', 'N') === 'Y') {
                        if ($author->extAttribs['useAvatar']->cAttribValue === 'G') {
                            $params = ['email' => null, 's' => 80, 'd' => 'mm', 'r' => 'g'];
                            $url    = 'https://www.gravatar.com/avatar/';
                            $url   .= md5(!empty($author->extAttribs['useGravatarEmail']->cAttribValue)
                                ? strtolower(trim($author->extAttribs['useGravatarEmail']->cAttribValue))
                                : strtolower(trim($author->cMail)));
                            $url   .= '?' . http_build_query($params, '', '&');
                            $author->cAvatarImgSrc = $url;
                        }
                        if ($author->extAttribs['useAvatar']->cAttribValue === 'U') {
                            $author->cAvatarImgSrc = $author->extAttribs['useAvatarUpload']->cAttribValue;
                        }
                    } elseif (isset($author->extAttribs['useAvatar'])) {
                        $author->extAttribs['useAvatar']->cAttribValue = 'N';
                    }
                    unset($author->extAttribs['useAvatarUpload'], $author->extAttribs['useGravatarEmail']);

                    // Vita benutzen?
                    if (isset($author->extAttribs['useVita_' . $_SESSION['cISOSprache']]) && $this->getConfigParam('use_vita', 'N') === 'Y') {
                        $author->cVitaShort = $author->extAttribs['useVita_' . $_SESSION['cISOSprache']]->cAttribValue;
                        $author->cVitaLong  = $author->extAttribs['useVita_' . $_SESSION['cISOSprache']]->cAttribText;
                    }
                    foreach (gibAlleSprachen() as $sprache) {
                        unset($author->extAttribs['useVita_' . $sprache->cISO]);
                    }

                    // Google+ benutzen?
                    if (!empty($author->extAttribs['useGPlus']->cAttribValue) && $this->getConfigParam('use_gplus', 'N') === 'Y') {
                        $author->cGplusProfile = $author->extAttribs['useGPlus']->cAttribValue;
                    }
                    unset($author->extAttribs['useGPlus']);

                    $contentArr[$key]->oAuthor = $author;
                }
            }
        }
    }

    /**
     * HOOK_BACKEND_ACCOUNT_PREPARE_EDIT
     *
     * @param stdClass $oAccount
     * @param JTLSmarty $smarty
     * @param array $attribs
     * @return string
     */
    public function getContent(stdClass $oAccount, JTLSmarty $smarty, array $attribs)
    {
        $showAvatar          = $this->getConfigParam('use_avatar', 'N') === 'Y';
        $showVita            = $this->getConfigParam('use_vita', 'N') === 'Y';
        $showGPlus           = $this->getConfigParam('use_gplus', 'N') === 'Y';
        $showSectionPersonal = $showAvatar || $showVita || $showGPlus;

        if ($showAvatar) {
            $gravatarEmail = '';
            if (!empty($attribs['useGravatarEmail']->cAttribValue)) {
                $gravatarEmail = $attribs['useGravatarEmail']->cAttribValue;
            } elseif (isset($oAccount->cMail)) {
                $gravatarEmail = $oAccount->cMail;
            }
            $uploadImage   = isset($attribs['useAvatar']->cAttribValue) &&
            $attribs['useAvatar']->cAttribValue === 'U' &&
            !empty($attribs['useAvatarUpload']->cAttribValue)
                ? $attribs['useAvatarUpload']->cAttribValue
                : '/' . BILD_UPLOAD_ZUGRIFF_VERWEIGERT;
        } else {
            $gravatarEmail = '';
            $uploadImage   = '';
        }

        $sprachen       = gibAlleSprachen();
        $defaultAttribs = [
            'useGravatarEmail' => (object)['cAttribValue' => ''],
            'useAvatar'        => (object)['cAttribValue' => ''],
            'useAvatarUpload'  => (object)['cAttribValue' => ''],
            'useGPlus'         => (object)['cAttribValue' => ''],
        ];
        foreach ($sprachen as $sprache) {
            $defaultAttribs['useVita_' . $sprache->cISO] = (object)[
                'cAttribValue' => '',
                'cAttribText'  => '',
            ];
        }
        $attribs = array_merge($defaultAttribs, $attribs);

        $result = $smarty
            ->assign('oAccount', $oAccount)
            ->assign('showAvatar', $showAvatar)
            ->assign('showVita', $showVita)
            ->assign('showGPlus', $showGPlus)
            ->assign('sectionPersonal', $showSectionPersonal)
            ->assign('gravatarEmail', $gravatarEmail)
            ->assign('uploadImage', $uploadImage)
            ->assign('attribValues', $attribs)
            ->assign('sprachen', $sprachen)
            ->fetch($this->plugin->cAdminmenuPfad . 'templates/userextension_index.tpl');

        return $result;
    }

    /**
     * HOOK_BACKEND_ACCOUNT_EDIT - VALIDATE
     *
     * @param stdClass $oAccount
     * @param array $attribs
     * @param array $messages
     * @return mixed bool|array - true if success otherwise errormap
     */
    public function validateAccount(stdClass $oAccount, array &$attribs, array &$messages)
    {
        $result = true;

        if ($this->getConfigParam('use_avatar', 'N') === 'Y') {
            if (!$attribs['useAvatar']) {
                $attribs['useAvatar'] = 'N';
            }

            switch ($attribs['useAvatar']) {
                case 'G':
                    if (!empty($attribs['useAvatarUpload'])) {
                        $this->deleteImage($attribs['useAvatarUpload']);
                        $attribs['useAvatarUpload'] = '';
                    }
                    break;
                case 'U':
                    $attribs['useGravatarEmail'] = '';

                    if (isset($_FILES['extAttribs']) && !empty($_FILES['extAttribs']['name']['useAvatarUpload'])) {
                        $attribs['useAvatarUpload'] = $this->uploadImage($_FILES['extAttribs'], 'useAvatarUpload');

                        if ($attribs['useAvatarUpload'] === false) {
                            $messages['error'] .= 'Fehler beim Bilupload!';
                            $result = ['useAvatarUpload' => 1];
                        }
                    } else {
                        if (empty($attribs['useAvatarUpload'])) {
                            $messages['error'] .= 'Bitte geben Sie ein Bild an!';
                            $result = ['useAvatarUpload' => 1];
                        }
                    }

                    break;
                default:
                    $attribs['useGravatarEmail'] = '';

                    if (!empty($attribs['useAvatarUpload'])) {
                        $this->deleteImage($attribs['useAvatarUpload']);
                        $attribs['useAvatarUpload'] = '';
                    }
            }
        }

        if ($this->getConfigParam('use_vita', 'N') === 'Y') {
            foreach (gibAlleSprachen() as $sprache) {
                $useVita_ISO = 'useVita_' . $sprache->cISO;

                if (!empty($attribs[$useVita_ISO])) {
                    $shortText = StringHandler::filterXSS($attribs[$useVita_ISO]);
                    $longtText = $attribs[$useVita_ISO];

                    if (strlen($shortText) > 255) {
                        $shortText = substr($shortText, 0, 250) . '...';
                    }

                    $attribs[$useVita_ISO] = [$shortText, $longtText];
                }
            }
        }

        return $result;
    }
}
