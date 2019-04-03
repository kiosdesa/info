import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { OfanCoreShareModule } from '../../module/ofancore-share.module';
import { OfanCoreShareIconsModule } from '../../module/ofancore-share.icon.modul';
import { IonicPageModule } from 'ionic-angular';
import { OrderPage } from './orders';

@NgModule({
  declarations: [
    OrderPage
  ],
  imports: [
    IonicPageModule.forChild(OrderPage),
    TranslateModule.forChild(), 
    OfanCoreShareModule,
    OfanCoreShareIconsModule
  ],
})
export class OrderPageModule {}
