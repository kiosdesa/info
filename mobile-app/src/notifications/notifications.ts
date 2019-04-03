import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, NavController, NavParams, MenuController } from 'ionic-angular';
import { RequestApiProvider, Online } from './../../providers/providers';

@IonicPage()
@Component({
  selector: 'page-notifications',
  templateUrl: 'notifications.html',
})
export class NotificationsPage 
{
  _dataNotif: any;
  _showBlank: boolean = false;

  constructor(
    private api: RequestApiProvider, 
    private online: Online, 
    public translate: TranslateService, 
    public navCtrl: NavController, 
    public navParams: NavParams, 
    public menuCtrl: MenuController) 
  {
    this.loadNotif()
  }
  
  ionViewDidEnter() 
  {
    this.menuCtrl.swipeEnable(false);
  }

  ionViewWillLeave() 
  {
    this.menuCtrl.swipeEnable(true);
  }

  async loadNotif()
  {
    const endpoint = 'notifApp';
    await this.api.post('app', endpoint, {init:'app-notif', pack:{fieldForm:{notif_message:true}}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      const theItems = Resp.items;
      if(theItems.approve == true)
      {
        this._showBlank = false;
        this._dataNotif = theItems.notif;
      }
      else
      {
        this._showBlank = true;
        this._dataNotif = undefined;
      }
    },
    (err)=>{
      this._showBlank = true;
      this.online.checkOnline(true)
    })
  }

  openPage(page, param?:any)
  {
    const params = param ? {paramRoots:param} : {};
    this.navCtrl.push(page, params, {animate:true})
  }
}
