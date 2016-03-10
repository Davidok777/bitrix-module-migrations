<?php

namespace WS\Migrations\Tests\Cases;


use WS\Migrations\Builder\Entity\Property;
use WS\Migrations\Builder\IblockBuilder;
use WS\Migrations\Tests\AbstractCase;

class IblockBuilderCase extends AbstractCase {

    public function name() {
        return $this->localization->message('name');
    }

    public function description() {
        return $this->localization->message('description');
    }

    public function init() {

        \CModule::IncludeModule('iblock');
    }

    public function close() {
        return;
        $iblock = \CIBlock::GetList(null, array(
            '=CODE' => 'testAddBlock'
        ))->Fetch();
        if ($iblock) {
            \CIBlock::Delete($iblock['ID']);
        }
        \CIBlockType::Delete('testAddType');
    }


    public function testAdd() {
        $iblockBuilder = new IblockBuilder();
        $iblockBuilder
            ->addIblockType('testAddType')
            ->setLang(array(
                'ru' => array(
                    'NAME' => 'Тестовый тип иб'
                ),
            ))
            ->setSort(10)
            ->setInRss(false)
        ;
        $iblockBuilder
            ->addIblock('testAddBlock')
            ->setIblockTypeId('testAddType')
            ->setSort(100)
            ->setName('Теcтовый иб')
            ->setVersion(2)
            ->setSiteId('s1')
            ->setGroupId(array(
                '2' => 'R'
            ))
        ;

        $iblockBuilder
            ->addProperty('Цвет')
            ->setType(Property::TYPE_NUMBER)
            ->setIsRequired(true)
            ->setMultiple(true)
            ->setCode('color')
            ;
        $iblockBuilder
            ->addProperty('Картинка')
            ->setType(Property::TYPE_FILE)
            ;
        $iblockBuilder->commit();

        $arType = \CIBlockType::GetList(null, array(
            'IBLOCK_TYPE_ID' => 'testAddType')
        )->Fetch();

        $this->assertNotEmpty($arType, "iblockType wasn't created");

        $arIblock = \CIBlock::GetList(null, array(
            'ID' => $iblockBuilder->getIblock()->getId()
        ))->Fetch();

        $this->assertNotEmpty($arIblock, "iblock wasn't created");
        $this->assertEquals($arIblock['CODE'], $iblockBuilder->getIblock()->code);
        $this->assertEquals($arIblock['NAME'], $iblockBuilder->getIblock()->name);
        $this->assertEquals($arIblock['SORT'], $iblockBuilder->getIblock()->sort);
        $this->assertEquals($arIblock['LID'], $iblockBuilder->getIblock()->siteId);

        $properties = \CIBlockProperty::GetList(null, array(
            'IBLOCK_ID' => $iblockBuilder->getIblock()->getId()
        ));
        $props = array(
            'Картинка' => array(
                'PROPERTY_TYPE' => 'F'
            ),
            'Цвет' => array(
                'PROPERTY_TYPE' => 'N',
                'IS_REQUIRED' => 'Y',
                'MULTIPLE' => 'Y',
            ),
        );
        while ($property = $properties->Fetch()) {
            $this->assertNotEmpty($props[$property['NAME']]);
            if ($property['NAME'] == 'Картинка') {
                $this->assertEquals($props[$property['NAME']]['PROPERTY_TYPE'], $property['PROPERTY_TYPE']);
            }

            if ($property['NAME'] == 'Цвет') {
                $this->assertEquals($props[$property['NAME']]['PROPERTY_TYPE'], $property['PROPERTY_TYPE']);
                $this->assertEquals($props[$property['NAME']]['IS_REQUIRED'], $property['IS_REQUIRED']);
                $this->assertEquals($props[$property['NAME']]['MULTIPLE'], $property['MULTIPLE']);
            }
        }

    }

    public function testUpdateIblockType() {
        $iblockBuilder = new IblockBuilder();
        $type = $iblockBuilder
            ->updateIblockType('testAddType')
            ->setSort(20)
        ;
        $iblockBuilder->commit();

        $arType = \CIBlockType::GetList(null, array(
                'IBLOCK_TYPE_ID' => 'testAddType')
        )->Fetch();

        $this->assertEquals($arType['SORT'], $type->sort);
    }

    public function testUpdate() {
        $iblockBuilder = new IblockBuilder();
        $iblockBuilder
            ->updateIblock('testAddBlock')
            ->setSort(200)
            ->setName('Теcтовый иб2')
            ->setVersion(2)
            ->setSiteId('s1')
            ->setGroupId(array(
                '2' => 'W'
            ))
        ;

        $iblockBuilder
            ->updateProperty('Цвет')
            ->setType(Property::TYPE_STRING, Property::USER_TYPE_USER)
        ;
        $iblockBuilder
            ->updateProperty('Картинка')
            ->setType(Property::TYPE_STRING)
            ->setCode('pic')
        ;
        $iblockBuilder->commit();

        $arIblock = \CIBlock::GetList(null, array(
            'ID' => $iblockBuilder->getIblock()->getId()
        ))->Fetch();


        $this->assertEquals($arIblock['CODE'], $iblockBuilder->getIblock()->code);
        $this->assertEquals($arIblock['NAME'], $iblockBuilder->getIblock()->name);
        $this->assertEquals($arIblock['SORT'], $iblockBuilder->getIblock()->sort);

        $properties = \CIBlockProperty::GetList(null, array(
            'IBLOCK_ID' => $iblockBuilder->getIblock()->getId()
        ));
        $props = array(
            'Картинка' => array(
                'PROPERTY_TYPE' => 'S',
                'CODE' => 'pic',
            ),
            'Цвет' => array(
                'PROPERTY_TYPE' => 'S',
                'USER_TYPE' => 'UserID',
            ),
        );
        while ($property = $properties->Fetch()) {
            $this->assertNotEmpty($props[$property['NAME']]);
            if ($property['NAME'] == 'Картинка') {
                $this->assertEquals($props[$property['NAME']]['PROPERTY_TYPE'], $property['PROPERTY_TYPE']);
                $this->assertEquals($props[$property['NAME']]['CODE'], $property['CODE']);
            }

            if ($property['NAME'] == 'Цвет') {
                $this->assertEquals($props[$property['NAME']]['PROPERTY_TYPE'], $property['PROPERTY_TYPE']);
                $this->assertEquals($props[$property['NAME']]['USER_TYPE'], $property['USER_TYPE']);
            }
        }

    }

}