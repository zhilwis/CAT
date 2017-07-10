<?php

namespace web\lib\admin\domain;

use web\lib\admin\view\html\UnaryTag;
use web\lib\admin\view\UserCredentialsForm;

/**
 * 
 * @author Zilvinas Vaira
 *
 */
class SilverbulletInvitation extends PersistentEntity {

    const TABLE = 'silverbullet_invitation';

    /**
     * Required profile identifier
     * 
     * @var string
     */
    const PROFILEID = 'profile_id';

    /**
     * Required user identifier
     * 
     * @var string
     */
    const SILVERBULLETUSERID = 'silverbullet_user_id';

    /**
     *
     * @var string
     */
    const TOKEN = 'token';

    /**
     *
     * @var string
     */
    const QUANTITY = 'quantity';
    
    /**
     *
     * @var string
     */
    const EXPIRY = 'expiry';

    /**
     * 
     * @var string
     */
    private $defaultTokenExpiry;

    /**
     * 
     * @param $silverbulletUser SilverbulletUser
     */
    public function __construct($silverbulletUser) {
        parent::__construct(self::TABLE, self::TYPE_INST);
        $this->setAttributeType(self::PROFILEID, Attribute::TYPE_INTEGER);
        $this->setAttributeType(self::SILVERBULLETUSERID, Attribute::TYPE_INTEGER);
        $this->setAttributeType(self::QUANTITY, Attribute::TYPE_INTEGER);
        if (!empty($silverbulletUser)) {
            $this->set(self::PROFILEID, $silverbulletUser->getProfileId());
            $this->set(self::SILVERBULLETUSERID, $silverbulletUser->getIdentifier());
            $this->set(self::TOKEN, $this->generateToken());
            $this->defaultTokenExpiry = date('Y-m-d H:i:s', strtotime("+1 week"));
            $this->set(self::EXPIRY, $this->defaultTokenExpiry);
        }
    }
    
    /**
     * 
     * @param int $quantity
     */
    public function setQuantity($quantity){
        $this->set(self::QUANTITY, $quantity);
    }

    /**
     *
     * @return string
     */
    public function getToken() {
        if ($this->isExpired()) {
            return _('User did not consume the token and it expired!');
        } else {
            return $this->get(self::TOKEN);
        }
    }

    /**
     * 
     * @param string $host
     * @return string
     */
    public function getTokenLink($host = '') {
        $link = '';
        if (empty($host)) {
            if (isset($_SERVER['HTTPS'])) {
                $link = 'https://';
            } else {
                $link = 'http://';
            }
            $link .= $_SERVER['SERVER_NAME'];
            $relPath = dirname(dirname($_SERVER['SCRIPT_NAME']));
            if ($relPath[strlen($relPath) -1] == '/') {
                $relPath = substr($relPath, 0, strlen($relPath) - 1);
            }
            $link = $link . $relPath;
        } else {
            $link = $host;
        }
        if ($this->isExpired()) {
            $link = _('User did not consume the token and it expired!');
        } else {
            $link .= '/accountstatus/accountstatus.php?token=' . $this->get(self::TOKEN);
            $input = new UnaryTag('input');
            $input->addAttribute('type', 'text');
            $input->addAttribute('class', UserCredentialsForm::INVITATION_TOKEN_CLASS);
            $input->addAttribute('readonly', 'readonly');
            $input->addAttribute('value', $link);
            $input->addAttribute('style', 'color: grey;min-width:150px;width:50%');
            $input->addAttribute('name', 'certificate-link[]');

            $link = $input->__toString();
        }
        return $link;
    }

    /**
     *
     * @return string
     */
    private function generateToken() {
        return hash("sha512", base_convert(rand(0, (int) 10e16), 10, 36));
    }

    /**
     * 
     * @return string
     */
    public function getExpiry() {
        if (empty($this->get(self::EXPIRY))) {
            return "n/a";
        } else {
            return date('Y-m-d', strtotime($this->get(self::EXPIRY)));
        }
    }

    /**
     *
     * @return boolean
     */
    public function isExpired() {
        $expiryTime = strtotime($this->get(self::EXPIRY));
        $currentTime = time();
        return $currentTime > $expiryTime;
    }

    /**
     * 
     * @param int $invitationId
     * @return \web\lib\admin\domain\SilverbulletInvitation
     */
    public static function prepare($invitationId) {
        $instance = new SilverbulletInvitation(null);
        $instance->set(self::ID, $invitationId);
        return $instance;
    }

    /**
     * 
     * @param SilverbulletUser $silverbulletUser
     * @param Attribute $searchAttribute
     * @return \web\lib\admin\domain\SilverbulletInvitation[]
     */
    public static function getList($silverbulletUser = null, $searchAttribute = null) {
        $databaseHandle = \core\DBConnection::handle(self::TYPE_INST);
        $userId = $silverbulletUser->getAttribute(self::ID);
        if ($searchAttribute != null && $silverbulletUser != null) {
            $query = sprintf("SELECT * FROM `%s` WHERE `%s`=? AND `%s`=? ORDER BY `%s` DESC", self::TABLE, self::SILVERBULLETUSERID, $searchAttribute->key, self::EXPIRY);
            $result = $databaseHandle->exec($query, $userId->getType() . $searchAttribute->getType(), $userId->value, $searchAttribute->value);
        } else if($silverbulletUser != null) {
            $query = sprintf("SELECT * FROM `%s` WHERE `%s`=? ORDER BY `%s` DESC", self::TABLE, self::SILVERBULLETUSERID, self::EXPIRY);
            $result = $databaseHandle->exec($query, $userId->getType(), $userId->value);
        } else if ($searchAttribute != null) {
            $query = sprintf("SELECT * FROM `%s` WHERE `%s`=? ORDER BY `%s` DESC", self::TABLE, $searchAttribute->key, self::EXPIRY);
            $result = $databaseHandle->exec($query, $searchAttribute->getType(), $searchAttribute->value);
        } else {
            $query = sprintf("SELECT * FROM `%s` ORDER BY `%s` DESC", self::TABLE, self::EXPIRY);
            $result = $databaseHandle->exec($query);
        }

        $list = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $invitation = new SilverbulletInvitation(null);
            $invitation->setRow($row);
            $list[] = $invitation;
        }
        return $list;
    }

}
