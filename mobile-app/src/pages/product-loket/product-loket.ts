import { Component } from '@angular/core';
import { IonicPage, NavController, Platform, ToastController, NavParams, Events } from 'ionic-angular';
import { TranslateService } from '@ngx-translate/core';
import { MenuProvider, NotifyProvider, SmartAudioProvider } from '../../providers/providers';

@IonicPage()
@Component({
  selector: 'page-product-loket',
  templateUrl: 'product-loket.html',
})
export class ProductLoketPage 
{
  topRightMenu: any;
  _pageParam: any;
  _nameCategory: any;
  _serverIcon: any;
	_showBlank: boolean = false;
  _cart_notif:any;
  _bell_notif:any;
  
  constructor(
		public navParams: NavParams, 
		public notif: NotifyProvider, 
		public toastCtrl: ToastController, 
		public platform: Platform, 
		public menuProv: MenuProvider,
		public navCtrl: NavController, 
		public translate: TranslateService,
		public events: Events, 
		public smartAudio: SmartAudioProvider) 
  {
    this._showBlank = true;
    let _paramRoots = this.navParams.get('paramRoots');
    this._pageParam = _paramRoots;
    this._nameCategory = _paramRoots.name;
    this._serverIcon = _paramRoots.server;
    this.loadCountNotif();
  }

	loadCountNotif()
	{
    this.notif.get('bell').then(val=>{this._bell_notif = val})
    this.notif.get('cart').then(val=>{this._cart_notif = val})
    setTimeout(()=>{this.setNotif()}, 500)
	}

  setNotif()
  {
    this.topRightMenu = this.menuProv.topBarMenu({cart:this._cart_notif, bell:this._bell_notif});
  }

	openPage(page)
	{
		this.navCtrl.push(page.component,
			{
				paramRoots:
				{
					id:page.id,
					slug:page.slug,
					name:page.name,
					component:page.component
				}
			},
			{
			animate:true,
			direction: 'enter'
			}
		);
  }

  searchTyping(e)
  {
  }

  searchPress(key)
  {
  }
}
