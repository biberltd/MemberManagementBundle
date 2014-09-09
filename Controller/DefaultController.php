<?php

namespace BiberLtd\Bundle\MemberManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BiberLtdMemberManagementBundle:Default:index.html.twig', array('name' => $name));
    }
}
