import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

export interface tabInterface
{
	component: any;
}

@Injectable()
export class MenuProvider 
{
	constructor( 
    	public translate: TranslateService) 
	{}

	loadTitle()
	{
		let textual: any;
		this.translate.get([
			'HOME_TITLE',
			'KIOS_NEWS',
			//'SHOP_USER_TITLE',
			'USER_ACCOUNT_TITLE',
			'ADVICE_TITLE',
			'CALENDAR_TITLE',
			'SETTINGS_TITLE'
		])
		.subscribe(values => {
	      textual = values;
	    });
	    return textual;
	}

	topMenu(trans?: any, notif?: any)
	{
		let notif1 = notif ? notif.dashboard : undefined;
		let notif2 = notif ? notif.account : undefined;
		return [
	    	{ title: trans.HOME_TITLE, component: 'HomeTabPage', notif:notif1, color:'white', root:false, icon:'md-speedometer', index: 0, param: null },
			{ title: trans.USER_ACCOUNT_TITLE, component: 'AccountPage', notif:notif2, color:'white', root:false, icon:'person', index: 3, param: null }
	    ];
	}

	standarMenu(trans?: any, notif?: any)
	{
		//let notif1 = notif ? notif.news : undefined;
		let notif2 = notif ? notif.advice : undefined;
		let notif3 = notif ? notif.news : undefined;
	    return [
	    	//{ title: trans.SHOP_USER_TITLE, component: 'BuyerProfilePage', notif:notif1, color:'white', root:false, index: undefined, icon:'md-shirt', param: null },
			{ title: trans.ADVICE_TITLE, component: 'AdvicesPage', notif:notif2, color:'white', root:false, index: undefined, icon:'ios-mail', param: null },
	    	{ title: trans.KIOS_NEWS, component: 'NewsPage', notif:notif3, color:'white', root:false, index: undefined, icon:'ios-paper', param: null }
	    ];
	}

	appMenu(trans?: any, notif?: any)
	{
		let notif1 = notif ? notif.setting : undefined;
		return [
			{ title: trans.SETTINGS_TITLE, component: 'SettingsPage', notif:notif1, color:'white', root:false, index: undefined, icon:'ios-cog', param: null }
	    ];
	}

	menuToggle(trans?: any, notif?: any)
	{
		trans = trans ? trans : this.loadTitle();
		notif = notif ? notif : undefined;
		let menuParking = this.topMenu(trans, notif);
	    return menuParking.concat(this.standarMenu(trans, notif), this.appMenu(trans, notif));
	}

	dashMenu(trans?: any, notif?: any)
	{
		trans = trans ? trans : this.loadTitle();
		notif = notif ? notif : undefined;
		let menuParking = this.standarMenu(trans, notif);
		return menuParking;
	    //return menuParking.concat(this.appMenu(trans, notif));
	}

	menuData(trans?: any, notif?: any)
	{
		trans = trans ? trans : this.loadTitle();
		notif = notif ? notif : undefined;
	    return this.standarMenu(trans, notif);
	}

	topBarMenu(notif?:any, section?: any)
	{
		let notif1 = notif ? notif.cart : undefined;
		let notif2 = notif ? notif.bell : undefined;
		let searchMenu = [{ id:0, slug: 'search', name: 'Search', component: 'SearchPage', icon: 'ios-search' }];
		let cartMenu = [{ id:1, slug: 'cart', name: 'Cart', component: 'CartsPage', icon: 'ios-cart', index: 2, notif:notif1 }];
		let notifMenu = [{ id:2, slug: 'notifications', name: 'Notifications', component: 'NotificationsPage', icon: 'ios-notifications', index: undefined, notif:notif2 }];
		if(section == 'home')
		{
			return searchMenu.concat(notifMenu);
		}
		else if(section == 'notif')
		{
			return notifMenu;
		}
		else if(section == 'cart')
		{
			return cartMenu;
		}
		else
		{
			return cartMenu.concat(notifMenu);
		}
	}

	bottomBarMenu()
	{
		return [
		  {name:'Category', section:'category', method: 'modal', icon:'ios-list-outline'},
		  {name:'Sorting', section:'sort', method: 'modal', icon:'ios-funnel-outline'},
		  {name:'Filter List', section:'filter', method: 'modal', icon:'ios-options-outline'},
		  {name:'More Menu', section:'more', method: 'menu', icon:'ios-more-outline'},
		];
	}
}
