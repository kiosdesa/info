import { Component, ViewChild } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, NavController, ModalController, NavParams, App, MenuController, Events, Tabs } from 'ionic-angular';
import { NativePageTransitions, NativeTransitionOptions } from '@ionic-native/native-page-transitions';
import { HomePage, FavoritesPage, CartsPage, BuyerProfilePage } from '../pages';
import { NotifyProvider } from '../../providers/providers';

@IonicPage()
@Component({
  selector: 'page-home-tab',
  templateUrl: 'home-tab.html',
})
export class HomeTabPage 
{
  loaded: boolean = false;
  tabIndex: number  = 0;
  tab1Root: any = HomePage;
  tab2Root: any = FavoritesPage;
  tab3Root: any = CartsPage;
  tab4Root: any = BuyerProfilePage;
  tab5Root: any = HomePage;

  _currentParam: any;
	_cartCount: any = 0;

  @ViewChild('homeTab') tabRef: Tabs;

  constructor(
    private app: App,
    private nativePageTransitions: NativePageTransitions,
    public menuCtrl: MenuController,
    public events: Events, 
    public modalCtrl: ModalController,
    public navCtrl: NavController, 
    public navParams: NavParams, 
    public translateService: TranslateService,
    public notif: NotifyProvider) 
  {
    this._currentParam = this.navParams.get('paramRoots');

    this.events.subscribe('tab:clicked', (data) => 
    {
      this.tabRef.select(data['tab']);
      if(data['tab'] == 2)
      {
        this.zeroCart()
      }
    })

    this.events.subscribe('tab:count', (data)=>{
      this.loadCountCart()
    })
  }

  ionViewDidLoad()
  {
    this.loadCart()
  }

  loadCart(idTab?:any)
  {
    if(idTab == 2)
    {
      this.zeroCart()
    }
    else
    {
      this.loadCountCart()
    }
  }
  
  loadCountCart()
  {
    this.notif.get('cart').then(val=>{
      this._cartCount = val;
    })
  }

  zeroCart()
  {
    this._cartCount = 0; 
  }

  private getAnimationDirection(index:number):string 
  {
    var currentIndex = this.tabIndex;
    this.tabIndex = index;

    switch (true)
    {
      case (currentIndex < index):
        return('left');
      case (currentIndex > index):
        return('right');
    }
  }

  public transition(e:any):void 
  {    
    let options: NativeTransitionOptions = {
      direction:this.getAnimationDirection(e.index),
      duration: 250,
      slowdownfactor: -1,
      slidePixels: 0,
      iosdelay: 20,
      androiddelay: 0,
      fixedPixelsTop: 0,
      fixedPixelsBottom: 48
    };

    if (!this.loaded) 
    {
      this.loaded = true;
      return;
    }

    this.nativePageTransitions.slide(options);
  }

  openPage(page) 
  {
    this.app.getRootNav().setRoot(page, {param:this._currentParam}, {animate:true,direction:'forward'});
  }

  openMenu()
  {
    this.menuCtrl.open();
  }
}
