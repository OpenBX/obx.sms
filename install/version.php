<?
/*****************************************
 ** @product Market-Start Bitrix Module **
 ** @vendor A68 Studio                  **
 ** @mailto info@a-68.ru                **
 *****************************************/

$arModuleVersion = array(
	"VERSION" => "1.1.2",
	"VERSION_DATE" => "2013-10-25",
);
return $arModuleVersion;

/**
 * [1.0.0]
 * * Стабилизирован релиз 1.0.0
 *
 * [1.1.0]
 * * Учтены изменения в базовых классах Settings obx.core
 * * Добавлена возможность подклюения lang-файлов провайдеров из папки /bitrix/php_infaterface/obx.sms/lang/...
 * * Доработана страница настроек модуля
 * ? Добавлены провайдеры
 *   ?- LetsAds (Россия + Украина + Белоруссия. + Казахстан. + Азербайджан + Грузия)
 *   ?- IqSms - СМС Дисконт (Россия)
 *   ?- TurboSMS.ua (Украина)
 *   ?- ByteHand.com
 *       Россия
 *       Зона 1: Армения, Беларусь, Грузия, Казахстан, Киргизия, Литва, Таджикистан, Туркменистан, Узбекистан, Абхазия.
 *       Зона 2: Афганистан, Алжир, Андорра, Ангола, Азербайджан, Аргентина, Бахрейн, Бангладеш, Боливия,
 *               Босния и Герцеговина, Камбоджа, Камерун, Канада, Шри-Ланка, Чили, Колумбия, Конго, Коста-Рика,
 *               Хорватия, Кипр, Эквадор, Сальвадор, Эстония, Габон, Палестинские территории, Гана, Гренландия,
 *               Гватемала, Гонконг, Исландия, Индия, Индонезия, Иран, Ирак, Ирландия, Израиль, Ямайка, Япония,
 *               Иордания, Кения, Кувейт, Лаос, Либерия, Лихтенштейн, Люксембург, Мадагаскар, Малайзия,
 *               Мальдивские о-ва, Мальта, Мексика, Монголия, Черногория, Пакистан, Парагвай, Филиппины,
 *               Саудовская Аравия, Сербия, Сингапур, Словакия, Вьетнам, Словения, ЮАР, Таиланд, ОАЭ,
 *               Тунис, Македония, Египет, Танзания, США, Уругвай, Венесуэла.
 *       Зона 3: Болгария, Китай, Тайвань, Куба, Чехия, Дания, Финляндия, Греция,
 *               Республика Корея, Латвия, Молдова, Польша, Великобритания.
 *       Зона 4: Все остальные страны
 *   ?- littlesms.ru
 *        Россия
 *        СНГ:   Украина, Абхазия, Армения, Беларусь, Грузия, Казахстан,
 *               Киргизия, Литва, Таджикистан, Туркменистан, Узбекистан.
 *        Зона 1: Азербайджан, Алжир, Ангола, Андорра, Аргентина, Афганистан, Бангладеш, Бахрейн, Боливия,
 *                Босния и Герцеговина, Венесуэла, Вьетнам, Габон, Гана, Гватемала, Гонконг, Гренландия, Египет,
 *                Израиль, Индия, Индонезия, Иордания, Ирак, Иран, Ирландия, Исландия, Камбоджа, Камерун, Канада,
 *                Кения, Кипр, Колумбия, Конго, Коста-Рика, Кувейт, Лаос, Либерия, Лихтенштейн, Люксембург,
 *                Мадагаскар, Македония, Малайзия, Мальдивы, Мальта, Мексика, Монголия, ОАЭ, Парагвай,
 *                Сальвадор, Саудовская Аравия, Сербия, Сингапур, Словакия, Словения, США, Таиланд, Танзания,
 *                Тунис, Уругвай, Филиппины, Хорватия, Черногория, Чили, Шри-Ланка, Эквадор, Эстония, ЮАР, Ямайка, Япония.
 *        Зона 2: Англия, Болгария, Греция, Дания, Куба, Латвия, Молдавия,
 *                Пакистан, Польша, Тайвань, Финляндия, Чехия, Южная Корея.
 *        Зона 3: Все остальные страны.
 */

